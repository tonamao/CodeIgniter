<?php
class CardController extends CI_Model {

	public static $TRUMP;
	public static $DAIFUGO;

	public function __construct() {
		$this->load->helper('url_helper');
		
		//TODO: database.phpを直す
		CardController::$TRUMP = $this->load->database('default',true);
		CardController::$DAIFUGO = $this->load->database('daifugo', true);
	}

	/**
	 * Get first player's hands.
	 * Insert 'daifugo_hand' DB.
	 * 
	 * @param int $playerNum
	 * @return Array $allHandLists 
	 * 			[0] : user, [1~$playerNum]: cpu
	 * 			[0] => Array (
	 *				[0] => Array ( [37] => assets/img/cards/heart_11.png )
	 * 				[1] => Array ( [26] => assets/img/cards/diamond_13.png )...
	 * 				[n] => Array ( {card id} => {card img path})
	 *			)...
	 */
	public function getFirstHandsLists($playerNum) {
		//全カードを順番にListに詰める
		//[0]=>1 [1]=>2 [2]=>3...
		$cardsInOrder = array();
		$query = CardController::$DAIFUGO->query('SELECT card_id FROM ms_trump_card');
		foreach ($query->result() as $row) {
			array_push($cardsInOrder, $row->card_id);
		}

		//ランダムに並べ変えてプレイヤーごとに手札をListにする
		$allPlayerCardsList = array();
		$selectedCard = array();
		for ($i = 0; $i < $playerNum; $i++) {
			//一人分のカードを格納するリスト
			$singleCards = array();
			$cnt = 0;
			while ($cnt < floor(54/$playerNum)) {
				$randomIndex = rand(0, 53);

				if (count($selectedCard) == 0) {//1回目
					//ランダムで選んだcard_id => $cardsInOrder[$randomIndex]
					$id = $cardsInOrder[$randomIndex];
					array_push($singleCards, $id);
					array_push($selectedCard, $randomIndex);
					$cnt++;
				} else {//2回目以降
					//すでに選んだカードと被らないように判定する
					$IsSelectedCard = in_array($randomIndex, $selectedCard);
					if (!$IsSelectedCard) {//すでに選んだカードと被ってなかったら
						$id = $cardsInOrder[$randomIndex];
						array_push($singleCards, $id);
						array_push($selectedCard, $randomIndex);
						$cnt++;
					}
				}
			}
			array_push($allPlayerCardsList, $singleCards);
		};

		//余りがある場合は余りをランダムにプレイヤーに振り分ける
		if (54%$playerNum > 0) {
			//余ったカードをListにつめる
			$restCardList = array();
			for ($i = 0; $i < 54; $i++) {
				$isContained = in_array($i, $selectedCard);
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
					array_push($allPlayerCardsList[$randomPlayerIndex], $id);
					array_push($selectedPlayers, $randomPlayerIndex);
					$cnt++;
					$cardIndex++;
				} else {//2回目以降
					$IsfoundSameNo = in_array($randomPlayerIndex, $selectedPlayers);
					if (!$IsfoundSameNo) {
						$id = $restCardList[$cnt];
						array_push($allPlayerCardsList[$randomPlayerIndex], $id);
						array_push($selectedPlayers, $randomPlayerIndex);
						$cnt++;
						$cardIndex++;
					}
				}
			}
		}
		//TODO output hand list($allPlayerCardsList) log

		//insert DB
		//TODO get game_id from 'daifugo_matching'
		$gameId = 1;
		foreach ($allPlayerCardsList as $playerId => $playerHandArray) {
			foreach ($playerHandArray as $key => $id) {
				$cardData = array(
					'game_id' => $gameId,
					'user_id' => $playerId,
					'card_id' => $id,
					'used_flg' => false
				);
				CardController::$DAIFUGO->insert('daifugo_hand', $cardData);
			}
		}
		//TODO output result of insert DB log

		//convert id array to id & card img path array;
		$imgPathListOfHands = array();
		for ($i = 0; $i < $playerNum; $i++) {
			$handQuery = CardController::$DAIFUGO->get_where(
					'daifugo_hand', array('user_id' => $i));
			$singleHand = array();
			foreach ($handQuery->result() as $handRow) {
				$cardId = $handRow->card_id;
				// $cardQuery = CardController::$DAIFUGO->query("SELECT card_id, card_name FROM ms_trump_card");
				$cardQuery = CardController::$DAIFUGO->get_where(
					'ms_trump_card', array('card_id' => $cardId));
				$idPath = array($cardId => 'assets/img/cards/'.$cardQuery->row()->card_name.'.png');
				array_push($singleHand, $idPath);
			}
			array_push($imgPathListOfHands, $singleHand);
		}
		return $imgPathListOfHands;
	}


	/**
	 * Return img path of card's back.
	 * @return String path of card's back
	 */
	public function getCardBack() {
		return 'assets/img/cards/back.png';
	}

	//TODO get num of players from daifugo_matching
	public function getNumOfPlayer() {
		return 4;
	}

////////////////////////////////////////////////////////////
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