<?php
class CardController extends CI_Model {

	public static $TRUMP;
	public static $DAIFUGO;

	public function __construct() {
		$this->load->helper('url_helper');
		
		CardController::$TRUMP = $this->load->database('default',true);
		CardController::$DAIFUGO = $this->load->database('daifugo', true);
	}

	/**
	 * @param int $playerNum
	 * @return Array $allHandLists 
	 * 			[0] : user, [1~$playerNum]: cpu
	 * 			array([0] => ('card id' => 'card img path'),
	 * 				  [1] => ('card id' => 'card img path'),...)
	 */
	public function getFirstHandLists($playerNum) {
		//全カードを順番にListに詰める
		$cardsInOrder = array();
		$query = CardController::$TRUMP->query('SELECT card_id FROM card');
		foreach ($query->result() as $row) {
			if(($val = $row->card_id) != '55'){
				array_push($cardsInOrder, $val);
			}
		}

		//ランダムに並べ変えてプレイヤーごとの手札をListにする
		$allUserCardsList = array();
		$selectedNo = array();
		for ($i = 0; $i < $playerNum; $i++) {
			//一人分のカードを格納するリスト
			$singleCards = array();
			$cnt = 0;
			while ($cnt < floor(54/$playerNum)) {
				$randomIndex = rand(0, 53);

				if (count($selectedNo) == 0) {//1回目
					//ランダムで選んだcard_id => $cardsInOrder[$randomIndex]
					$id = $cardsInOrder[$randomIndex];
					$query = CardController::$TRUMP->get_where('card', array('card_id' => $id));
					$path = 'assets/img/cards/'.$query->row()->card_img.'.png';
					$idPath = array("$id" => "$path");
					array_push($singleCards, $idPath);
					array_push($selectedNo, $randomIndex);
					$cnt++;
				} else {//2回目以降
					//すでに選んだインデックスと被らないように判定する
					$IsfoundSameNo = in_array($randomIndex, $selectedNo);
					if (!$IsfoundSameNo) {//すでに選んだインデックスと被ってなかったら
						$id = $cardsInOrder[$randomIndex];
						$query = CardController::$TRUMP->get_where('card', array('card_id' => $id));
						$path = 'assets/img/cards/'.$query->row()->card_img.'.png';
						$idPath = array("$id" => "$path");
						array_push($singleCards, $idPath);
						array_push($selectedNo, $randomIndex);
						$cnt++;
					}
				}
			}
			array_push($allUserCardsList, $singleCards);
		};

		//余りがある場合は余りをランダムにプレイヤーに振り分ける
		if (54%$playerNum > 0) {
			//余ったカードをListにつめる
			$restCardList = array();
			for ($i = 0; $i < 54; $i++) {
				$isContained = in_array($i, $selectedNo);
				if (!$isContained) {
					array_push($restCardList, $cardsInOrder[$i]);
				}
			}

			$selectedPlayers = array();
			$cnt = 0;
			$cardIndex = floor(54/$playerNum);
			while ($cnt < count($restCardList)) {
				$randomPlayerIndex = rand(0, ($playerNum - 1));

				if (count($selectedPlayers) == 0) {//1回目
					$id = $restCardList[$cnt];
					$query = CardController::$TRUMP->get_where('card', array('card_id' => $id));
					$path = 'assets/img/cards/'.$query->row()->card_img.'.png';
					$restIdPath = array("$id" => "$path");
					array_push($allUserCardsList[$randomPlayerIndex], $restIdPath);
					array_push($selectedPlayers, $randomPlayerIndex);
					$cnt++;
					$cardIndex++;
				} else {//2回目以降
					$IsfoundSameNo = in_array($randomPlayerIndex, $selectedPlayers);
					if (!$IsfoundSameNo) {
						$id = $restCardList[$cnt];
						$query = CardController::$TRUMP->get_where('card', array('card_id' => $id));
						$path = 'assets/img/cards/'.$query->row()->card_img.'.png';
						$restIdPath = array("$id" => "$path");
						array_push($allUserCardsList[$randomPlayerIndex], $restIdPath);
						array_push($selectedPlayers, $randomPlayerIndex);
						$cnt++;
						$cardIndex++;
					}
				}
			}
		}
		return $allUserCardsList;
	}

	/**
	 * @param int $gameNum, array $allHandLists
	 * すべてのプレイヤーの手札をDBに登録する
	 */
	public function insertHands($gameNum, $allHandLists) {

		foreach ($allHandLists as $playerId => $playerHands) {
			foreach ($playerHands as $key => $idPath) {
				foreach ($idPath as $cardId => $path) {
					$cardData = array(
						'player_id' => $playerId,
						'card_id' => $cardId,
						'game_id' => $gameNum,
						'used_id' => 0,
						'discard_flg' => false
					);
					CardController::$DAIFUGO->insert('daifugo_card', $cardData);
				}
			}
		}
	}

	/**
	 * @return String path of card's back
	 * カードの裏面のパスを出す
	 */
	public function getCardBack() {
		return 'assets/img/cards/back.png';
	}

	//TODO: 
	/**
	 * @return 
	 * 
	 */
	public function checkCards($selectingCards) {
		//ルールをチェックする
		return true;
	}

	//TODO: used_idを動的に
	/**
	 * 場に出したカードを使用済みとして、used_idをtrueで更新する
	 */
	public function useCard($gameNum, $selectingCards, $usedId) {
		$idList = explode(',', $selectingCards);
		foreach ($idList as $id) {
		 	CardController::$DAIFUGO->set('used_id', $usedId);
		 	CardController::$DAIFUGO->where('card_id', $id);
		 	CardController::$DAIFUGO->update('daifugo_card');
		 }
	}

	/**
	 * @param int $gameNum
	 * @param int $playerNum
	 * @return Array $allHandLists 
	 * 			[0] : user, [1~$playerNum]: cpu
	 * 			array([0] => ('card id' => 'card img path')...)
	 * DBの情報から、未使用（used_id==0）のカードを再表示する
	 */
	public function getHandLists($gameNum, $playerNum) {
		$handLists = array();
		for ($i = 0; $i < $playerNum; $i++) {
			$query = CardController::$DAIFUGO->select('card_id')->get_where('daifugo_card', array('game_id' => $gameNum, 'player_id' => $i, 'used_id' => 0));
			$idList = $query->result();
			$singleCards = array();
			foreach ($idList as $row) {
				$id = $row->card_id;
				$query = CardController::$TRUMP->get_where('card', array('card_id' => $id));
				$path = 'assets/img/cards/'.$query->row()->card_img.'.png';
				$idPath = array("$id" => "$path");
				array_push($singleCards, $idPath);
			}
			array_push($handLists, $singleCards);
		}
		return $handLists;
	}

	public function getUsedCard($gameNum) {
		$query = CardController::$DAIFUGO->select('card_id')->get_where('daifugo_card', array('game_id' => $gameNum, 'used_id >' => 0, 'discard_flg' => false));
		$idList = $query->result();
		$singleCards = array();
		foreach ($idList as $row) {
			$id = $row->card_id;
			$query = CardController::$TRUMP->get_where('card', array('card_id' => $id));
			$path = 'assets/img/cards/'.$query->row()->card_img.'.png';
			$idPath = array("$id" => "$path");
			array_push($singleCards, $idPath);
		}
		return $singleCards;
	}
}