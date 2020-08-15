<?php
class GameMatching extends CI_Model {
	public function __construct() {
		$this->load->helper('url_helper');
		$this->load->database();
	}

	/**
	 * insert game mathing
	 */
	public function insertGameMatching($gameName) {
		// create game
		$insertData = array(
			'player_1' => 'user0', //TODO: プレイヤー情報は動的に入れる
			'player_2' => 'cpu1',
			'player_3' => 'cpu2',
			'player_4' => 'cpu3',
			'playing_flg' => true,
		);
		$this->db->insert('daifugo_matching', $insertData);

	}

	//TODO get num of players from daifugo_matching
	public function getNumOfPlayer() {
		return 4;
	}

	/**
	 * get game id by user id
	 * @param  String $userId
	 * @return String $gameId
	 */
	public function getGameIdByUserId($userId) {
		// TODO: ユーザに紐づくゲーム情報が複数になった場合は修正する
		return $this->db->get_where('daifugo_matching', array('player_1' => $userId, 'playing_flg' => true))->row()->game_id;
	}
}