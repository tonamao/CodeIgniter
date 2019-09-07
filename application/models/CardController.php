<?php
class CardController extends CI_Model {

	public static $MARKS = ['club', 'diamond', 'heart', 'spade'];

	public function __construct() {
		$this->load->helper('url_helper');
		
	}

	public function getCardsInOrder(){
		$CardArray = array();
		foreach (CardController::$MARKS as $mark) {
			for ($i = 0; $i < 13; $i++) {
				array_push($CardArray, 'assets/img/cards/'.$mark.'_'.($i + 1).'.png');
			}
		}
		array_push($CardArray, 'assets/img/cards/joker.png');
		array_push($CardArray, 'assets/img/cards/joker.png');
		return $CardArray;
	}

	//最初に配られるカードをプレイヤー数分Listにして返却
	public function getRandomCardsList($player_num, $cardsInOrder) {
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

	public function initCard() {
		
		$cards = array();
		foreach (CardController::$marks as $mark) {
			for ( $i = 0; $i < 3; $i++) {
				array_push($cards, 'assets/img/cards/'.$mark.'_'.($i + 1).'.png');
			}
		}
		return $cards;
	}

	public function getCardBack() {
		return 'assets/img/cards/back.png';
	}

}