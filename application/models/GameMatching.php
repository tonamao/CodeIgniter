<?php
class GameMatching extends CI_Model {

	public static $TRUMP;
	public static $DAIFUGO;

	public function __construct() {
		$this->load->helper('url_helper');
		
		//TODO: database.phpを直す
		GameMatching::$TRUMP = $this->load->database('default',true);
		GameMatching::$DAIFUGO = $this->load->database('daifugo', true);
	}

	/**
	 * insert game mathing
	 */
	public function insertGameMatching($gameName) {
		//TODO: get table name, game name from like const class;
		if ($gameName == 'DFG') $table = 'daifugo_matching';

		//TODO get all user ids
		$userIds = array('user0', 'cpu1', 'cpu2', 'cpu3');
		//TODO get user category
		$userCategoryIds = array('user', 'cpu', 'cpu', 'cpu');

		//TODO get max cnt of game id
		$maxGameIdCnt = 0;
		$cnt = 0;
		$order = 1;
		foreach ($userIds as $key => $userId) {
			$insertData = array(
				'game_id' => $gameName.$maxGameIdCnt,
				'user_id' => $userId,
				'user_category_id' => $userCategoryIds[$cnt++],
				'game_order' => $order++,
				'playing_flg' => true
			);
			GameMatching::$DAIFUGO->insert($table, $insertData);
		}
	}

	//TODO get num of players from daifugo_matching
	public function getNumOfPlayer() {
		return 4;
	}
}