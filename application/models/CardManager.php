<?php
class CardManager extends CI_Model {
	public static $GAME_NAME = 'DFG';

	public function __construct() {
		$this->load->helper('url_helper');
		$this->load->database();
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
		$query = $this->db->query('SELECT card_id FROM ms_trump_card');
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
			while ($cnt < floor(54 / $playerNum)) {
				$randomIndex = rand(0, 53);

				if (count($selectedCard) == 0) {
					//1回目
					//ランダムで選んだcard_id => $cardsInOrder[$randomIndex]
					$id = $cardsInOrder[$randomIndex];
					array_push($singleCards, $id);
					array_push($selectedCard, $randomIndex);
					$cnt++;
				} else {
					//2回目以降
					//すでに選んだカードと被らないように判定する
					$IsSelectedCard = in_array($randomIndex, $selectedCard);
					if (!$IsSelectedCard) {
						//すでに選んだカードと被ってなかったら
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
		if (54 % $playerNum > 0) {
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
			$cardIndex = floor(54 / $playerNum);
			while ($cnt < count($restCardList)) {
				$randomPlayerIndex = rand(0, ($playerNum - 1));

				if (count($selectedPlayers) == 0) {
					//1回目
					$id = $restCardList[$cnt];
					array_push($allPlayerCardsList[$randomPlayerIndex], $id);
					array_push($selectedPlayers, $randomPlayerIndex);
					$cnt++;
					$cardIndex++;
				} else {
					//2回目以降
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
		//TODO: user id(from session?)
		$userId = 'user0';
		$gameId = $this->db->get_where('user', array('user_id' => $userId))->row()->playing_game_id;
		$userIdArray = $this->db->get_where('daifugo_matching', array('game_id' => $gameId))->result_array();
		foreach ($allPlayerCardsList as $playerIndex => $playerHandArray) {
			$playerId = $userIdArray[$playerIndex]['user_id'];
			print_r($playerId, true);
			foreach ($playerHandArray as $key => $id) {
				$level = 0;
				if ((1 <= $id) && ($id <= 13)) {
					$cardNum = ($id - 13 * 0);
					if ($cardNum <= 2) {
						$level = ($cardNum + 11) * 4 - 3;
					} else {
						$level = ($cardNum - 2) * 4 - 3;
					}
				} else if ((14 <= $id) && ($id <= 26)) {
					$cardNum = ($id - 13 * 1);
					if ($cardNum <= 2) {
						$level = ($cardNum + 11) * 4 - 2;
					} else {
						$level = ($cardNum - 2) * 4 - 2;
					}
				} else if ((27 <= $id) && ($id <= 39)) {
					$cardNum = ($id - 13 * 2);
					if ($cardNum <= 2) {
						$level = ($cardNum + 11) * 4 - 1;
					} else {
						$level = ($cardNum - 2) * 4 - 1;
					}
				} else if ((40 <= $id) && ($id <= 52)) {
					$cardNum = ($id - 13 * 3);
					if ($cardNum <= 2) {
						$level = ($cardNum + 11) * 4 - 0;
					} else {
						$level = ($cardNum - 2) * 4 - 0;
					}
				} else if (($id == 53) || ($id == 54)) {
					$level = 53;
				}

				$cardData = array(
					'game_id' => $gameId,
					'user_id' => $playerId,
					'card_id' => $id,
					'used_flg' => false,
					'strength_level' => $level,
				);
				$this->db->insert('daifugo_hand', $cardData);
			}
		}
		//TODO output result of insert DB log

		//convert id array to id & card img path array;
		//TODO order card
		$imgPathListOfHands = array();
		for ($i = 0; $i < $playerNum; $i++) {
			$this->db->order_by('strength_level', 'ASC');
			$handQuery = $this->db->get_where(
				'daifugo_hand', array('user_id' => $userIdArray[$i]['user_id']));
			$singleHand = array();
			foreach ($handQuery->result() as $handRow) {
				$cardId = $handRow->card_id;
				$cardName = $this->db->get_where('ms_trump_card', array('card_id' => $cardId))->row()->card_name;
				$idPath = array($cardId => 'assets/img/cards/' . $cardName . '.png');
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

	/**
	 * update hand as used(used_flg = true)
	 */
	public function useCard($userId, $selectingCards) {
		$table = '';
		$gameId = $this->db->get_where('user', array('user_id' => $userId))->row()->playing_game_id;
		if (strpos($gameId, CardManager::$GAME_NAME) !== false) {
			$table = 'daifugo_hand';
		}

		foreach ($selectingCards as $cardId) {
			$this->db->set('used_flg', true);
			$this->db->where(array('game_id' => $gameId, 'card_id' => $cardId));
			$this->db->update($table);
		}
	}

	/**
	 * Get all player's hands.
	 *
	 * @return Array $allHandLists
	 * 			[0] : user, [1~$playerNum]: cpu
	 * 			[0] => Array (
	 *				[0] => Array ( [37] => assets/img/cards/heart_11.png )
	 * 				[1] => Array ( [26] => assets/img/cards/diamond_13.png )...
	 * 				[n] => Array ( {card id} => {card img path})
	 *			)...
	 */
	public function getLatestHand($playerNum, $userId) {
		$gameId = $this->db->get_where('user', array('user_id' => $userId))->row()->playing_game_id;
		$playerIdArray = $this->db->get_where('daifugo_matching', array('game_id' => $gameId))->result_array();
		$imgPathListOfHands = array();
		for ($i = 0; $i < $playerNum; $i++) {
			$this->db->order_by('strength_level', 'ASC');
			$handQuery = $this->db->get_where('daifugo_hand', array('user_id' => $playerIdArray[$i]['user_id'], 'used_flg' => false));
			$singleHand = array();
			foreach ($handQuery->result() as $handRow) {
				$cardId = $handRow->card_id;
				$cardName = $this->db->get_where('ms_trump_card', array('card_id' => $cardId))->row()->card_name;
				$idPath = array($cardId => 'assets/img/cards/' . $cardName . '.png');
				array_push($singleHand, $idPath);
			}
			array_push($imgPathListOfHands, $singleHand);
		}
		return $imgPathListOfHands;
	}

	/**
	 * get used cards
	 * @return used card array
	 * 			[0] => Array (
	 *				[0] => Array ( [37] => assets/img/cards/heart_11.png )
	 * 				[1] => Array ( [26] => assets/img/cards/diamond_13.png )...
	 * 				[n] => Array ( {card id} => {card img path})
	 *			)...
	 */
	public function getUsedCards() {
		$allUsedCards = array();
		//TODO: get user id
		$userId = 'user0';
		$table = '';
		$gameId = $this->db->get_where('user', array('user_id' => $userId))->row()->playing_game_id;
		if (strpos($gameId, CardManager::$GAME_NAME) !== false) {
			$table = 'daifugo_game_area_card';
		}

		//card idの連想配列を作る
		$allCardIdsArray = array();
		$singleCardIdArray = array();
		$this->db->select('card_ids');
		$query = $this->db->get_where($table, array('game_id' => $gameId, 'discard_flg' => false));
		foreach ($query->result() as $row) {
			$singleCardIdArray = explode(':', $row->card_ids);
			array_push($allCardIdsArray, $singleCardIdArray);
		}

		//convert card id array to img path array
		$allUsedCards = array();
		foreach ($allCardIdsArray as $key => $cardIdArray) {
			$singleIdArray = array();
			foreach ($cardIdArray as $key => $cardId) {
				$cardName = $this->db->get_where('ms_trump_card', array('card_id' => $cardId))->row()->card_name;
				$idPath = array($cardId => 'assets/img/cards/' . $cardName . '.png');
				array_push($singleIdArray, $idPath);
			}
			array_push($allUsedCards, $singleIdArray);
		}
		return $allUsedCards;
	}

	/**
	 * test code for delete
	 */
	public function deleteAll() {
		$this->db->empty_table('daifugo_game_area_card');
		$this->db->empty_table('daifugo_game_manager');
		$this->db->empty_table('daifugo_hand');
		$this->db->empty_table('daifugo_matching');
		$this->db->empty_table('daifugo_user_status');
		$this->db->empty_table('daifugo_result');
		return true;
	}

	/**
	 * テストメソッド
	 * get selecting cardId & imgPath
	 * @return selecting card array
	 *				[0] => Array ( [37] => assets/img/cards/heart_11.png )
	 * 				[1] => Array ( [26] => assets/img/cards/diamond_13.png )...
	 * 				[n] => Array ( {card id} => {card img path})
	 */
	public function getSelectingCards($userId, $cardIdArrayStr) {

		log_message('debug', '---getSelectingCards---');
		$idList = array($cardIdArrayStr);
		if (strpos($cardIdArrayStr, ',') !== false) {
			log_message('debug', 'excluding "," target ->' . $cardIdArrayStr);
			$idList = explode(',', $cardIdArrayStr);
			log_message('debug', 'finish to explode');
			log_message('debug', print_r($idList, true));
		}
		$selectinhCardImgPathArray = array();
		foreach ($idList as $key => $cardId) {
			log_message('debug', 'card id :' . $cardId);
			$cardName = $this->db->get_where('ms_trump_card', array('card_id' => $cardId))->row()->card_name;
			log_message('debug', $cardName);
			$idPath = array($cardId => 'assets/img/cards/' . $cardName . '.png');
			array_push($selectinhCardImgPathArray, $idPath);
		}

		log_message('debug', '---getSelectingCards---');
		return $selectinhCardImgPathArray;
	}

	/**
	 * update selecting card's used_flg to true
	 * @param  int $userId
	 * @param  Array $selectingCardArray
	 */
	public function updateSelectingCards($userId, $selectingCardArray) {
		log_message('debug', 'CardManager argArray: ' . print_r($selectingCardArray, true));
		if (empty($userId) || empty($selectingCardArray)) {
			return null;
		}

		$gameId = $this->db->get_where('user', array('user_id' => $userId))->row()->playing_game_id;
		$table = '';
		if (strpos($gameId, CardManager::$GAME_NAME) !== false) {
			$table = 'daifugo_hand';
		}

		foreach ($selectingCardArray as $cardId) {
			log_message('debug', 'cardId : ' . $cardId);
			$this->db->set('used_flg', true);
			$this->db->where(array('game_id' => $gameId, 'card_id' => $cardId));
			$this->db->update($table);
		}

		// CardListを返す
		require_once 'Entity/Card.php';
		$cardList = array();
		foreach ($selectingCardArray as $cardId) {
			$cardName = $this->db->get_where('ms_trump_card', array('card_id' => $cardId))->row()->card_name;
			$idPath = 'assets/img/cards/' . $cardName . '.png';
			$card = new Card();
			$card->setId($cardId);
			$card->setCardImg($idPath);
			array_push($cardList, $card);
		}
		return $cardList;
	}

}
