<?php
class GameManager extends CI_Model {

	public static $TRUMP;
	public static $DAIFUGO;

	public static $GAME_DAIFUGO = 'DFG';

	public function __construct() {
		$this->load->helper('url_helper');
		
		//TODO: database.phpを直す
		GameManager::$TRUMP = $this->load->database('default',true);
		GameManager::$DAIFUGO = $this->load->database('daifugo', true);
	}

	/**
	 * insert game status (daifugo_game_manager)
	 */
	public function insertGameStatus($userId, $passFlg) {
		$table = '';
		$gameId = GameManager::$DAIFUGO->get_where('user_playing_game', array('user_id' => $userId))->row()->playing_game_id;
		$gameTurn = 1;
		$userTurn = $userId;
		$userEndFlg = false;
		$turnOwner = $userTurn;
		$passNum = $passFlg == true ? 1 : 0;
		$gameEndFlg = false;
		$gameStatusId = $gameId.'-T'.$gameTurn.'-S0';

		if (GameManager::$DAIFUGO->count_all('daifugo_game_manager') > 0) {
			GameManager::$DAIFUGO->where(array('game_id' => $gameId));
			$playerNum = GameManager::$DAIFUGO->count_all_results('daifugo_matching') > 0;

			$latestQuery = GameManager::$DAIFUGO->query('SELECT * FROM daifugo_game_manager WHERE insert_time = (SELECT MAX(insert_time) FROM daifugo_game_manager)');
			// $test = GameManager::$DAIFUGO->select('game_status_id, game_turn, pass_num, user_turn, turn_owner, MAX(insert_time)');

			//user turn
			$lastUserTurn = $latestQuery->row()->user_turn;
			$lastUserOrder = GameManager::$DAIFUGO->get_where('daifugo_matching', array('game_id' => $gameId, 'user_id' => $lastUserTurn))->row()->game_order;
			$order = $lastUserOrder;
			for ($i = 0; $i < ($playerNum - 1); $i++) {
				if ($lastUserOrder < $playerNum) {
					$order++;
				} else {
					$order = 1;
				}
				$targetUserId = GameManager::$DAIFUGO->get_where('daifugo_matching', array('game_id' => $gameId, 'game_order' => $order))->row()->user_id;
				$isUserEnd = GameManager::$DAIFUGO->get_where('daifugo_game_user_status', array('game_id' => $gameId, 'user_id' => $targetUserId))->row()->user_end_flg;
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
			GameManager::$DAIFUGO->where(array('user_end_flg' => false));
			$playingPlayerNum = GameManager::$DAIFUGO->count_all_results('daifugo_user_status');
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

			$lastGameTurn = $latestQuery->row()->game_turn;
			if ($passNum == $playingPlayerNum) {
				$gameTurn = ($lastGameTurn + 1);
			} else {
				$gameTurn = $lastGameTurn;
			}

			//game end flg
		 	GameManager::$DAIFUGO->where(array('game_id' => $gameId, 'used_flg' => false));
			if (GameManager::$DAIFUGO->count_all_results('daifugo_hand') == 0) {
				$gameEndFlg = true;
			}

			//game status id
			$lastGameStatusId = $latestQuery->row()->game_status_id;
			$statusCnt = substr($lastGameStatusId, (strpos($lastGameStatusId, 'S') + 1));
			$gameStatusId = $gameId.'-T'.$gameTurn.'-S'.(++$statusCnt);
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
		GameManager::$DAIFUGO->insert($table, $insertData);
	}

	/**
	 * get latest game status id
	 * @return gameStatusId
	 */
	public function getLatestGameStatus() {
		GameManager::$DAIFUGO->select_max('insert_time');
		GameManager::$DAIFUGO->select_max('game_status_id');
		$gameStatusId = GameManager::$DAIFUGO->get('daifugo_game_manager')->row()->game_status_id;
		return $gameStatusId;
	}

	/**
	 * insert user status (daifugo_user_status)
	 */
	public function insertUserStatus($latestGameStatusId, $userId, $passFlg) {
		$table = '';
		$gameId = GameManager::$DAIFUGO->get_where('daifugo_game_manager', array('game_status_id' => $latestGameStatusId))->row()->game_id;

		$userEndFlg;
		GameManager::$DAIFUGO->where(array('user_id' => $userId, 'used_flg' => false));
		if (GameManager::$DAIFUGO->count_all_results('daifugo_hand') > 0) {
			$userEndFlg = false;
		} else {
			$userEndFlg = true;
		}

		if (strpos($gameId, GameManager::$GAME_DAIFUGO) !== false) $table = 'daifugo_user_status';
		$insertData = array(
			'game_status_id' => $latestGameStatusId,
			'game_id' => $gameId,
			'user_id' => $userId,
			'pass_flg' => $passFlg,
			'user_end_flg' => $userEndFlg
		);
		GameManager::$DAIFUGO->insert($table, $insertData);
	}

	/**
	 * check whether latest player is end or not
	 * @return user end flg
	 */
	public function checkUserEnd($latestGameStatusId) {
		return GameManager::$DAIFUGO->get_where('daifugo_user_status', array('game_status_id' => $latestGameStatusId))->row()->user_end_flg;
	}
}