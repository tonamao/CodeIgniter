<?php
class GameAreaManager extends CI_Model {

	public static $GAME_NAME = 'DFG';

	public function __construct() {
		$this->load->helper('url_helper');
		$this->load->database();
	}

	/**
	 * insert game area status (daifugo_game_area_card)
	 */
	public function updateGameAreaStatus($passFlg, $latestGameStatusId, $selectingCards) {
		$table = '';
		$latestGameId = $this->db->get_where('daifugo_game_manager', array('game_status_id' => $latestGameStatusId))->row()->game_id;
		$latestGameTurn = $this->db->get_where('daifugo_game_manager', array('game_status_id' => $latestGameStatusId))->row()->game_turn;
		$latestPassNum = $this->db->get_where('daifugo_game_manager', array('game_status_id' => $latestGameStatusId))->row()->pass_num;

		if (strpos($latestGameId, GameAreaManager::$GAME_NAME) !== false) {
			$table = 'daifugo_game_area_card';
		}

		if ($passFlg) {
			//case pass(update)
			if ($latestPassNum == 0) {
				$updateData = array(
					'discard_flg' => true,
				);
				$this->db->where(array('game_id' => $latestGameId, 'game_turn' => $latestGameTurn));
				$this->db->update($table, $updateData);
			}
		} else {
			//case put(insert)
			$selectingCardIds = '';
			foreach ($selectingCards as $cardId) {
				$selectingCardIds .= $cardId;
				$selectingCardIds .= ':';
			}
			$selectingCardIds = substr($selectingCardIds, 0, -1);

			$insertData = array(
				'game_status_id' => $latestGameStatusId,
				'game_id' => $latestGameId,
				'game_turn' => $latestGameTurn,
				'card_ids' => $selectingCardIds,
				'discard_flg' => false,
			);
			$this->db->insert($table, $insertData);
		}
	}
}