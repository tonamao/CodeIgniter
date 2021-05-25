<?php
class GameManager extends CI_Model {
	public static $GAME_DAIFUGO = 'DFG';

	public function __construct() {
		$this->load->helper('url_helper');
		$this->load->database();
	}

	/**
	 * insert game status, turn status (daifugo_game_status, daifugo_turn_status)
	 */
	public function insertGameStatus($userId, $passFlg) {
		$gameId = $this->db->get_where('tb_daifugo_matching', array('player_1' => 'user0', 'playing_flg' => true))->row()->game_id;
		$gameTurn = 1;
		$turnOwner = $userId;
		$passNum = 0;
		$gameEndFlg = false;
		$playerNum = 4; //TODO: 動的に取れるようにする

		if ($this->db->count_all('tb_daifugo_game_status') > 0) {
			$this->db->where(array('game_id' => $gameId));

			$this->db->limit(1)
				->order_by('insert_time', 'DESC');
			$latestRow = $this->db->get('tb_daifugo_game_status')->row();

			//turn owner
			if ($passFlg) {
				$turnOwner = $latestRow->turn_owner;
			}

			//pass num
			if ($passFlg) {
				$passNum = $latestRow->pass_num + 1;
			}

			//game end flg
			$numOfHnadPerUser = $this->db->select('count(*) as count')
				->where('game_id', $gameId)
				->group_by('user_id')
				->get('tb_daifugo_hand')->result_array();

			$cnt = 0;
			foreach ($numOfHnadPerUser as $key => $num) {
				if ($num == 0) {
					$cnt++;
				}
			}
			if ($cnt == ($playerNum - 1)) {
				$gameEndFlg = true;
			}
		}

		// insert game status
		$gameStatusData = array(
			'game_id' => $gameId,
			'game_end_flg' => $gameEndFlg,
		);
		$this->db->insert('tb_daifugo_game_status', $gameStatusData);

		// turn end flg
		$turnEndFlg = $passNum == ($playerNum - 1) ? true : false;
    /**
    $this->db->limit(1)
			->order_by('insert_time', 'DESC');
		$insertedRow = $this->db->get('tb_daifugo_game_status')->row();
    */

		// insert turn status
		$turnStatusData = array(
			'game_id' => $gameId,
			'turn_user' => $userId,
			'turn_owner' => $turnOwner,
			'pass_num' => $passNum,
			'turn_end_flg' => $turnEndFlg,
		);
		$this->db->insert('tb_daifugo_turn_status', $turnStatusData);
	}

	/**
	 * get latest game status id
	 * @return gameStatusId
	 */
	public function getLatestGameStatus() {
		$latestQuery = $this->db->query('SELECT game_status_id, MAX(updated_at) FROM tb_daifugo_turn_status;');
		$gameStatusId = $latestQuery->row()->game_status_id;
		return $gameStatusId;
	}

	/**
	 * if num of record is 0, insert user status.
	 * else, update user status. (daifugo_user_status)
	 */
	public function insertUserStatus($latestGameStatusId, $userId, $userEndFlg) {
		log_message('debug', 'GameManager::insertUserStatus() Start');
		$gameId = $this->db->get_where('tb_daifugo_game_status', array('id' => $latestGameStatusId))->row()->game_id;

		$insertData = array(
			'game_status_id' => $latestGameStatusId,
			'game_id' => $gameId,
			'user_id' => $userId,
			'user_end_flg' => $userEndFlg,
		);
		$this->db->insert('tb_daifugo_user_status', $insertData);
		log_message('debug', 'GameManager::insertUserStatus() End');
	}

	/**
	 * check whether latest player is end or not
	 * @return user end flg
	 */
	public function checkUserEnd($latestGameStatusId) {
		return $this->db->get_where('tb_daifugo_user_status', array('id' => $latestGameStatusId))->row()->user_end_flg;
	}

	public function getMasterTrumpCloverA() {
		return $this->db->get_where('ms_trump_card', array('card_id' => 1))->row()->card_name;
	}
}
