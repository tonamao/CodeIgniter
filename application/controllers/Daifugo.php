<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daifugo extends CI_Controller {
	public static $GAME_NAME = 'DFG';

	public function __construct() {
		parent::__construct();
		$this->output->enable_profiler(false);
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

	public function rule() {
		//マッチングを状態を登録する
		$this->gameMatching->insertGameMatching(Daifugo::$GAME_NAME);

		//TODO: ルール情報取得

		//TODO: ルール情報表示

		$this->load->view('daifugo/rule-selection');
	}

	/**
	 * Insert daifugo_matching.
	 * Hand out cards.
	 * Check player's class & exchange cards.
	 * Insert daifugo_hand.
	 * Display first player hands.
	 */
	public function start() {
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
	//TODO: putとpassを同じpathにする（playing）
	public function put() {
		//TODO: get user ID(from session?)
		$userId = 'user0';
		//TODO get pass flg
		$passFlg = false;

		if (!$passFlg) {
			$selectingCards = $this->input->post('hidden-put');
			//update player's hands
			$this->cardManager->useCard($userId, $selectingCards);
		}

		$ruleList = $this->ruleManager->getRules();
		$isMatchingRules = $this->ruleManager->checkRules($ruleList, $selectingCards);
		//if match rules, update DB.
		if ($isMatchingRules) {
		}

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
		$playerNum = $this->gameMatching->getNumOfPlayer();
		$data['all_hands'] = $this->cardManager->getLatestHand($playerNum, $userId);
		$data['back'] = $this->cardManager->getCardBack();
		$data['game_area_cards'] = $this->cardManager->getUsedCards();

		//TODO:CPUが出すカードをランダムで生成
		//TODO: 出すカードが決まったらDB更新
		//TODO: 更新前の状態と更新後の状態をdataで渡す

		$this->load->view('daifugo/daifugo', $data);

	}

	/**
	 *
	 */
	public function pass() {
		//TODO: get user ID(from session?)
		$userId = 'user0';
		//TODO get pass flg
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

	/**
	 * test code for ajax + update db
	 */
	public function test() {
		log_message('debug', '---Daifugo Controller Start---');
		$userId = $this->input->post('userId');
		//TODO get pass flg
		$passFlg = boolval($this->input->post('passFlg'));
		log_message('debug', '$passFlg: ' . $passFlg);
		$selectingCards = $this->input->post('cards');
		log_message('debug', '$selectingCards is ' . print_r($selectingCards, true));
		if (!boolval($passFlg)) {
			// FIXME: update hands after check rules
			$this->cardManager->useCard($userId, $selectingCards);

			// check rules
			$ruleList = $this->ruleManager->getRules();
			$isMatchingRules = $this->ruleManager->checkRules($ruleList, $selectingCards);
			//if match rules, update DB.
			if ($isMatchingRules) {
				//update player's hands
				$this->cardManager->useCard($userId, $selectingCards);
			}
		}

		//update game status
		log_message('debug', 'insertGameStatus...' . $userId);
		$this->gameManager->insertGameStatus($userId, $passFlg);
		//update user(playing game) status
		log_message('debug', 'getLatestGameStatus...');
		$latestGameStatusId = $this->gameManager->getLatestGameStatus();
		$this->gameManager->updateUserStatus($latestGameStatusId, $userId, $passFlg);
		log_message('debug', 'updateUserStatus');
		//update game area cards
		$this->gameAreaManager->updateGameAreaStatus($passFlg, $latestGameStatusId, $selectingCards);

		//TODO: check whether user is end or not

		//TODO: check game end
		//TODO: if game is end, update DB
		//TODO : update matching
		//TODO : insert game_result

		// create response
		log_message('debug', 'create response');
		$playerNum = $this->gameMatching->getNumOfPlayer();
		$data['all_hands'] = $this->cardManager->getLatestHand($playerNum, $userId);
		$data['back'] = $this->cardManager->getCardBack();
		$data['game_area_cards'] = $this->cardManager->getUsedCards();
		$data['cards_used_in_current_turn'] = $this->cardManager->updateSelectingCards($userId, $selectingCards);
		log_message('debug', 'Response [CardList] is ' . print_r($data['cards_used_in_current_turn'], true));

		//TODO:CPUが出すカードをランダムで生成
		//TODO: 出すカードが決まったらDB更新
		//TODO: 更新後のarea-cardと次のターンのturnIdを詰める

		//$dataをJSONにして返す
		log_message('debug', '---Daifugo Controller---');
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($data));
	}

	/**
	 * test code for delete DB
	 */
	public function delete() {
		$result = $this->cardManager->deleteAll();
		echo $result == true ? 'Deletion completed!!' : 'Failed to delete DB...';
	}
}
