<?php
class CardController extends CI_Model {

	public static $TRUMP = null;
	public static $DAIFUGO;

	public function __construct() {
		$this->load->helper('url_helper');
		CardController::$TRUMP = $this->load->database('default',true);
		CardController::$DAIFUGO = $this->load->database('daifugo', true);
	}

	//最初に配られるカードをプレイヤー数分Listにして返却
	/**
	 * @param int $player_num [num of player]
	 * 
	 * @return Array $initCardList
	 */
	public function getRandomCardsList($player_num) {
		//DBから取得したカードを順番にimgパスとしてListに詰める
		$cardsInOrder = array();
		$query = CardController::$TRUMP->query('SELECT card_id FROM card');
		foreach ($query->result() as $row) {
			if(($val = $row->card_id) != '55'){
				array_push($cardsInOrder, $val);
			}
		}

		//順番に並んだカードをランダムに並べ変えてプレイヤーごとにListにする
		$firstCardsList = array();
		$selectedNo = array();

		for ($i = 0; $i < $player_num; $i++) {
			//一人分の初期カードを格納するリスト
			$singleFirstCard = array();

			$cnt = 0;
			while ($cnt < floor(54/$player_num)) {
				$randomIndex = rand(0, 53);

				if (count($selectedNo) == 0) {//1回目
					array_push($singleFirstCard, $cardsInOrder[$randomIndex]);
					array_push($selectedNo, $randomIndex);
					$cnt++;
				} else {//2回目以降
					//すでに選んだインデックスと被らないように判定する
					$IsfoundSameNo = in_array($randomIndex, $selectedNo);
					if (!$IsfoundSameNo) {//すでに選んだインデックスと被ってなかったら
						array_push($singleFirstCard, $cardsInOrder[$randomIndex]);
						array_push($selectedNo, $randomIndex);
						$cnt++;
					}
				}
			}
			array_push($firstCardsList, $singleFirstCard);
		};

		//余りがある場合は余りをランダムにプレイヤーに振り分ける
		if (54%$player_num > 0) {
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
			while ($cnt < count($restCardList)) {
				$randomPlayerIndex = rand(0, ($player_num - 1));

				if (count($selectedPlayers) == 0) {//1回目
					array_push($firstCardsList[$randomPlayerIndex], $restCardList[$cnt]);
					array_push($selectedPlayers, $randomPlayerIndex);
					$cnt++;
				} else {//2回目以降
					$IsfoundSameNo = in_array($randomPlayerIndex, $selectedPlayers);
					if (!$IsfoundSameNo) {
						array_push($firstCardsList[$randomPlayerIndex], $restCardList[$cnt]);
						array_push($selectedPlayers, $randomPlayerIndex);
						$cnt++;
					}
				}
			}
		}
		return $firstCardsList;
	}

	public function getCardBack() {
		return 'assets/img/cards/back.png';
	}

	public function insertCards($gameNo, $cardLists) {
		$cnt = 0;
		foreach ($cardLists as $cardList) {
			$id = $cnt + 1;
			foreach ($cardList as $card) {
				$cardData = array(
					'player_id' => $id,
					'card_id' => $card,
					'game_id' => $gameNo,
					'used_flg' => false
				);
				CardController::$DAIFUGO->insert('daifugo_card', $cardData);
			}
			$cnt++;
		}
	}

	public function toImgPath($id) {
		$query = CardController::$TRUMP->get_where('card', array('card_id' => $id));
		return 'assets/img/cards/'.$query->row()->card_img.'.png';
	}
}