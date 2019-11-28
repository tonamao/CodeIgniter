<?php
class GameAreaManager extends CI_Model {

	public static $TRUMP;
	public static $DAIFUGO;
	public static $GAME_NAME = 'DFG';

	public function __construct() {
		$this->load->helper('url_helper');
		
		//TODO: database.phpを直す
		GameAreaManager::$TRUMP = $this->load->database('default',true);
		GameAreaManager::$DAIFUGO = $this->load->database('daifugo', true);
	}

	/**
	 * insert game area status (daifugo_game_area_card)
	 */
	public function updateGameAreaStatus($passFlg, $latestGameStatusId, $selectingCards) {
		$table = '';
		$gameId = GameAreaManager::$DAIFUGO->get_where('daifugo_game_manager', array('game_status_id' => $latestGameStatusId))->row()->game_id;
		$gameTurn = GameAreaManager::$DAIFUGO->get_where('daifugo_game_manager', array('game_status_id' => $latestGameStatusId))->row()->game_turn;

		if (strpos($gameId, GameAreaManager::$GAME_NAME) !== false) $table = 'daifugo_game_area_card';
		if ($passFlg) {//case pass(update)
			$updateData = array(
				'discard_flg' => true
			);
			GameAreaManager::$DAIFUGO->where(array('game_id' => $gameId, 'game_turn' => $gameTurn));
			GameAreaManager::$DAIFUGO->update($table, $updateData);
		} else {//case put(insert)
			$selectingCardIds = '';
			$idList = explode(',', $selectingCards);
			foreach ($idList as $cardId) {
				$selectingCardIds .= $cardId;
				$selectingCardIds .= ':';
			 }
			 $selectingCardIds = substr($selectingCardIds, 0, -1);

			$insertData = array(
				'game_status_id' => $latestGameStatusId,
				'game_id' => $gameId,
				'game_turn' => $gameTurn,
				'card_ids' => $selectingCardIds,
				'discard_flg' => false
			);
			GameAreaManager::$DAIFUGO->insert($table, $insertData);
		}
	}
}