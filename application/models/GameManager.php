<?php
class GameManager extends CI_Model {
	public static $GAME_DAIFUGO = 'DFG';

	public function __construct() {
		$this->load->helper('url_helper');
        $this->load->database();
	}

	/**
	 * insert game status (daifugo_game_manager)
	 */
	public function insertGameStatus($userId, $passFlg) {
		$table = '';
		$gameId = $this->db->get_where('user', array('user_id' => $userId))->row()->playing_game_id;
		$gameTurn = 1;
		$userTurn = $userId;
		$userEndFlg = false;
		$turnOwner = $userTurn;
		$passNum = $passFlg == true ? 1 : 0;
		$gameEndFlg = false;
		$gameStatusId = $gameId.'-T'.$gameTurn.'-O'.$userId;//DFG0-T{gameTurn}-O{userId}

		if ($this->db->count_all('daifugo_game_manager') > 0) {
			$this->db->where(array('game_id' => $gameId));
			$playerNum = $this->db->count_all_results('daifugo_matching') > 0;

			$latestQuery = $this->db->query('SELECT * FROM daifugo_game_manager WHERE insert_time = (SELECT MAX(insert_time) FROM daifugo_game_manager)');

			//user turn
			$lastUserTurn = $latestQuery->row()->user_turn;
			$lastUserOrder = $this->db->get_where('daifugo_matching', array('game_id' => $gameId, 'user_id' => $lastUserTurn))->row()->game_order;
			$order = $lastUserOrder;
			for ($i = 0; $i < ($playerNum - 1); $i++) {
				if ($lastUserOrder < $playerNum) {
					$order++;
				} else {
					$order = 1;
				}
				$targetUserId = $this->db->get_where('daifugo_matching', array('game_id' => $gameId, 'game_order' => $order))->row()->user_id;
				$isUserEnd = $this->db->get_where('daifugo_game_user_status', array('game_id' => $gameId, 'user_id' => $targetUserId))->row()->user_end_flg;
				if (!$isUserEnd) {
					$userTurn = $targetUserId;
					break;
				}
			}

			//turn owner
			$turnOwner = $latestQuery->row()->turn_owner;
			if (!$passFlg) {
				$turnOwner = $userTurn;
			}

			//pass num & game turn
			$lastGameTurn = $latestQuery->row()->game_turn;
			$this->db->where(array('game_turn' => $lastGameTurn, 'user_end_flg' => false));
			$playingPlayerNum = $this->db->count_all_results('daifugo_user_status');
			$maxPassNum = ($playingPlayerNum - 1);
			$lastPassNum = $latestQuery->row()->pass_num;
			if ($passFlg) {
				$passNum = ($lastPassNum + 1);
				if ($passNum == $playingPlayerNum) {
					$passNum = 0;
				}
			} else {
				$passNum = $lastPassNum;
			}

			if ($passNum == $playingPlayerNum) {
				$gameTurn = ($lastGameTurn + 1);
			} else {
				$gameTurn = $lastGameTurn;
			}

			//game end flg
		 	$this->db->where(array('game_id' => $gameId, 'used_flg' => false));
			if ($this->db->count_all_results('daifugo_hand') == 0) {
				$gameEndFlg = true;
			}

			//game status id
			$lastGameStatusId = $latestQuery->row()->game_status_id;
			$statusCnt = substr($lastGameStatusId, (strpos($lastGameStatusId, 'S') + 1));
			$gameStatusId = $gameId.'-T'.$gameTurn.'-O'.(++$statusCnt);
		}

		//insert time
		date_default_timezone_set('Asia/Tokyo');
		$now = date('Y-m-d H:i:s');

		if (strpos($gameId, GameManager::$GAME_DAIFUGO) !== false) $table = 'daifugo_game_manager';
		$insertData = array(
			'game_status_id' => $gameStatusId,
			'game_id' => $gameId,
			'game_turn' => $gameTurn,
			'user_turn' => $userTurn,
			'turn_owner' => $turnOwner,
			'pass_num' => $passNum,
			'game_end_flg' => $gameEndFlg,
			'insert_time' => $now
		);
		$this->db->insert($table, $insertData);
	}

	/**
	 * get latest game status id
	 * @return gameStatusId
	 */
	public function getLatestGameStatus() {
		$latestQuery = $this->db->query('SELECT * FROM daifugo_game_manager WHERE insert_time = (SELECT MAX(insert_time) FROM daifugo_game_manager)');
		$gameStatusId = $latestQuery->row()->game_status_id;
		return $gameStatusId;
	}

	/**
	 * if num of record is 0, insert user status.
	 * else, update user status. (daifugo_user_status)
	 */
	public function updateUserStatus($latestGameStatusId, $userId, $passFlg) {
		$table = '';
		$gameId = $this->db->get_where('daifugo_game_manager', array('game_status_id' => $latestGameStatusId))->row()->game_id;
		$gameTurn = $this->db->get_where('daifugo_game_manager', array('game_status_id' => $latestGameStatusId))->row()->game_turn;

		$userEndFlg;
		$this->db->where(array('user_id' => $userId, 'used_flg' => false));
		if ($this->db->count_all_results('daifugo_hand') > 0) {
			$userEndFlg = false;
		} else {
			$userEndFlg = true;
		}

		if (strpos($gameId, GameManager::$GAME_DAIFUGO) !== false) $table = 'daifugo_user_status';
		$userStatusRecordNum = $this->db->count_all_results('daifugo_user_status');
		if ($userStatusRecordNum == 0) {
			$playerNum = $this->db->count_all_results('daifugo_matching');
			for ($i = 0; $i < $playerNum; $i++) {
				$userIdToInsert = $this->db->get_where('daifugo_matching',
					array('game_order' => ($i + 1)))->row()->user_id;
				if ($userId == $userIdToInsert) {
					$passFlgToInsert = $passFlg;
				} else {
					$passFlgToInsert = false;
				}
				$insertData = array(
					'game_status_id' => $latestGameStatusId,
					'game_id' => $gameId,
					'game_turn' => $gameTurn,
					'user_id' => $userIdToInsert,
					'pass_flg' => $passFlgToInsert,
					'user_end_flg' => $userEndFlg
				);
				$this->db->insert($table, $insertData);
			}
		} else {
			$updateData = array(
				'game_status_id' => $latestGameStatusId,
				'game_id' => $gameId,
				'game_turn' => $gameTurn,
				'user_id' => $userId,
				'pass_flg' => $passFlg,
				'user_end_flg' => $userEndFlg
			);
			$this->db->where(array('game_id' => $gameId, 'user_id' => $userId));
			$this->db->update($table, $updateData);
		}
	}

	/**
	 * check whether latest player is end or not
	 * @return user end flg
	 */
	public function checkUserEnd($latestGameStatusId) {
		return $this->db->get_where('daifugo_user_status', array('game_status_id' => $latestGameStatusId))->row()->user_end_flg;
	}
  
	public function getMasterTrumpCloverA() {
		return $this->db->get_where('ms_trump_card', array('card_id' => 1))->row()->card_name;
	}
}