<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daifugo extends CI_Controller {
	public static $GAME_NAME = 'DFG';

	public function __construct(){
		parent::__construct();
		$this->load->helper('url_helper');
		$this->load->helper('html');
		$this->load->model('gameMatching');
		$this->load->model('cardManager');
		$this->load->model('ruleManager');
		$this->load->model('gameManager');
		$this->load->model('gameAreaManager');
		$this->load->helper('form');
		$this->load->library('form_validation');

	}

	/**
	 * Insert daifugo_matching.
	 * Hand out cards.
	 * Check player's class & exchange cards.
	 * Insert daifugo_hand.
	 * Display first player hands.
	 */
	public function start() {
		$this->gameMatching->insertGameMatching(Daifugo::$GAME_NAME);

		//get all player's hands
		$playerNum = $this->gameMatching->getNumOfPlayer();
		$data['all_hands'] = $this->cardManager->getFirstHandsLists($playerNum);
		$data['back'] = $this->cardManager->getCardBack();
		$data['game_area_cards'] = array();

		//TODO: check player's class & exchange cards -> update hand DB

		$this->load->view('daifugo/daifugo', $data);
	}

	/**
	 * 
	 */
	public function put() {
		//TODO: get user ID(from session?)
		$userId = 'user0';
		$selectingCards = $this->input->post('hidden-put');

		$ruleList = $this->ruleManager->getRules();
		$isMatchingRules = $this->ruleManager->checkRules($ruleList, $selectingCards);

		//if match rules, update DB.
		if ($isMatchingRules) {
			//TODO get pass flg
			$passFlg = false;
			//TODO get user ID (from session?)
			$userId = 'user0';

			//update player's hands
			$this->cardManager->useCard($userId, $selectingCards);
			//update game status
			$this->gameManager->insertGameStatus($userId, $passFlg);
			//update user(playing game) status
			$latestGameStatusId = $this->gameManager->getLatestGameStatus();
			$this->gameManager->updateUserStatus($latestGameStatusId, $userId, $passFlg);
			//update game area cards
			$this->gameAreaManager->updateGameAreaStatus($passFlg, $latestGameStatusId, $selectingCards);		

			//TODO: check whether user is end or not

			//TODO: check game end
				//TODO: if game is end, update DB
				//update matching
				//insert game_result
		}
		$playerNum = $this->gameMatching->getNumOfPlayer();
		$data['all_hands'] = $this->cardManager->getLatestHand($playerNum, $userId);
		$data['back'] = $this->cardManager->getCardBack();
		$data['game_area_cards'] = $this->cardManager->getUsedCards();
		$this->load->view('daifugo/daifugo', $data);
	}

	/**
	 * test code
	 */
	public function test() {

		print_r('Test Completion!');
	}

	/**
	 * test code for delete DB
	 */
	public function delete() {
		$result = $this->cardManager->deleteAll();
		echo $result == true ? 'Deletion completed!!' : 'Failed to delete DB...';
	}

	/**
	 * 
	 */
	public function pass() {
		//TODO: get user ID(from session?)
		$userId = 'user0';
		$passFlg = true;

		//update game status
		$this->gameManager->insertGameStatus($userId, $passFlg);

		//update user(playing game) status
		$latestGameStatusId = $this->gameManager->getLatestGameStatus();
		$this->gameManager->updateUserStatus($latestGameStatusId, $userId, $passFlg);

		//update game area cards
		$this->gameAreaManager->updateGameAreaStatus($passFlg, $latestGameStatusId, null);

		//view
		$playerNum = $this->gameMatching->getNumOfPlayer();
		$data['all_hands'] = $this->cardManager->getLatestHand($playerNum, $userId);
		$data['back'] = $this->cardManager->getCardBack();
		$data['game_area_cards'] = $this->cardManager->getUsedCards();
		$this->load->view('daifugo/daifugo', $data);
	}

}
