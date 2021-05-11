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
		$this->load->model('masterDataManager');
		$this->load->helper('form');
		$this->load->library('form_validation');

	}

	public function rule() {
		//マッチングを状態を登録する
		$this->gameMatching->insertGameMatching(Daifugo::$GAME_NAME);

		//TODO: ルール情報取得
		$data['gameInfo'] = $this->masterDataManager->getGameInfo();

		//TODO: ルール情報表示

		$this->load->view('daifugo/rule-selection', $data);
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
		$data['all_hands'] = $this->cardManager->getFirstHandsLists($this->gameMatching->getNumOfPlayer());
		$data['back'] = $this->cardManager->getCardBack();
		$data['game_area_cards'] = array();

		//TODO: check player's class & exchange cards -> update hand DB

		$this->load->view('daifugo/daifugo', $data);
	}

	/**
	 * put process
	 * update player hands & game status, return ajax response
	 */
	public function put() {
		log_message('debug', '---Daifugo.put() Start---');

		log_message('debug', 'Daifugo::put() Start to update user status');
		// update user hand
		$userId = $this->input->post('userId');
		$userCardsPosted = $this->input->post('cards');
		$passFlg = false;

		// TODO: check rule
		// $isMatchingRules = $this->ruleManager->checkRules($this->ruleManager->getRules(), $selectingCards);
		$userEndFlg = false;
		$isMatchingRules = true;
		if ($isMatchingRules) {
			// $userEndFlg = $this->cardManager->updateCardToUsed($userId, $userCards);
			$userCardsObj = $this->cardManager->updateCardToUsed($userId, $userCardsPosted);
		}

		// update game status for user
		$userEndFlg = $this->cardManager->getPlayerEndFlg($userId);
		$latestGameStatusId = $this->updateGameStatus($userId, $userEndFlg, $passFlg);
		// update game area cards for user
		$this->gameAreaManager->insertGameAreaStatus($passFlg, $latestGameStatusId, $userCardsPosted);

		log_message('debug', 'Daifugo::put() End to update user status');

		// TODO: ユーザの終了判定
		// TODO:　ゲームの終了判定

		log_message('debug', 'Daifugo::put() Start to update CPUs status');

		$cpuNum = 3; //TODO: 動的に取得する
		$cpuCardNum = 2; //TODO: 動的に取得する
		$cpuCards = $this->cardManager->useCpuHands($this->gameMatching->getGameIdByUserId($userId), $cpuNum, $cpuCardNum); //TODO: 前の人が出したカードを見てカードを選ぶようにする
		foreach ($cpuCards as $cpuId => $cardArray) {

			// update CPU hand
			$cpuEndFlg = $this->cardManager->getPlayerEndFlg($cpuId);
			// update game status for user
			$passFlg = count($cardArray) == 0 ? true : false;
			$latestGameStatusId = $this->updateGameStatus($cpuId, $cpuEndFlg, $passFlg);
			// update game area cards for user
			$this->gameAreaManager->insertGameAreaStatus($passFlg, $latestGameStatusId, $cardArray);

		}

		log_message('debug', 'Daifugo::put() End to update CPUs status');

		// create response
		$data = $this->createPutResponse($userId, $userCardsObj, $this->cardManager->convertCpuCards($cpuCards));

		//TODO: 更新後の次のターンのturnIdを詰める

		//$dataをJSONにして返す
		log_message('debug', '---Daifugo.put() End---');
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($data));
	}

	/**
	 * pass process
	 * update only game status, return ajax data
	 */
	public function pass() {
		log_message('debug', '---Daifugo.pass() Start---');
		// get ajax data
		$userId = $this->input->post('userId');
		$passFlg = true;

		//update game status
		log_message('debug', 'insert GameStatus :' . $userId);
		$this->gameManager->insertGameStatus($userId, $passFlg);
		//update user(playing game) status
		log_message('debug', 'get LatestGameStatus');
		$latestGameStatusId = $this->gameManager->getLatestGameStatus();
		$this->gameManager->updateUserStatus($latestGameStatusId, $userId, $passFlg);
		log_message('debug', 'updateUserStatus');

		// create response
		log_message('debug', 'create response');
		$playerNum = $this->gameMatching->getNumOfPlayer();
		$data['all_hands'] = $this->cardManager->getLatestHand($playerNum, $userId);
		$data['back'] = $this->cardManager->getCardBack();
		$data['game_area_cards'] = $this->cardManager->getUsedCards();

		//TODO:CPUが出すカードをランダムで生成
		//TODO: 出すカードが決まったらDB更新
		//TODO: 更新後のarea-cardと次のターンのturnIdを詰める

		//$dataをJSONにして返す
		log_message('debug', '---Daifugo.pass() End---');
		$this->output
			->set_content_type('application/json')
			->set_output(json_encode($data));
	}

	private function updateGameStatus($playerId, $userEndFlg, $passFlg) {
		//insert game status
		$this->gameManager->insertGameStatus($playerId, $passFlg);
		//update user status
		$latestGameStatusId = $this->gameManager->getLatestGameStatus();
		if ($userEndFlg) {
			$this->gameManager->insertUserStatus($latestGameStatusId, $playerId, $userEndFlg);
		}
		return $latestGameStatusId;
	}

	private function createPutResponse($userId, $userCards, $cpuCards) {
		log_message('debug', '['.__LINE__.'] create response');
		$playerNum = $this->gameMatching->getNumOfPlayer();
		$data['all_hands'] = $this->cardManager->getLatestHand($playerNum, $userId);
		$data['back'] = $this->cardManager->getCardBack();
		$data['cards_used_in_current_turn'] = $userCards;
		$data['cards_cpu_used_in_current_turn'] = $cpuCards;
		$data['game_area_cards'] = $this->cardManager->getUsedCards();
		log_message('debug', 'Response [cards_used_in_current_turn]: ' . print_r($data['cards_used_in_current_turn'], true));
		log_message('debug', 'Response [cards_cpu_used_in_current_turn]: ' . print_r($data['cards_cpu_used_in_current_turn'], true));
		return $data;
	}

	////////////////////////////////////////
	///  test code                       ///
	////////////////////////////////////////

	/**
	 * test code for ajax + update db
	 */
  public function test() {
    ////////////////////////////////
    // Start To Update USER cards //
    ////////////////////////////////
		$userId = $this->input->post('userId');
		$userCardsPosted = $this->input->post('cards');
		$passFlg = false;

		// TODO: check rule
		$isMatchingRules = $this->ruleManager->checkRules($this->ruleManager->getRules(), $userCardsPosted);
		$userEndFlg = false;
    $userCardsObj = null;
    if ($isMatchingRules) {
			// $userEndFlg = $this->cardManager->updateCardToUsed($userId, $userCards);
		  // update user hand
			$userCardsObj = $this->cardManager->updateCardToUsed($userId, $userCardsPosted);// FIXME: gameId事前に取っておいて引数に入れる
		}

		// update game status for user
		$userEndFlg = $this->cardManager->getPlayerEndFlg($userId);
		$latestGameStatusId = $this->updateGameStatus($userId, $userEndFlg, $passFlg);
		// update game area cards for user
		$this->gameAreaManager->insertGameAreaStatus($passFlg, $latestGameStatusId, $userCardsPosted);

    ///////////////////////////////
    // Start To Update CPU cards //
    ///////////////////////////////
    $cpuNum = 3; //TODO: 動的に取得する
		$cpuCardNum = 2; //TODO: 動的に取得する
		$cpuCards = $this->cardManager->useCpuHands($this->gameMatching->getGameIdByUserId($userId), $cpuNum, $cpuCardNum); //TODO: 前の人が出したカードを見てカードを選ぶようにする
		foreach ($cpuCards as $cpuId => $cardArray) {
			// update CPU hand
			$cpuEndFlg = $this->cardManager->getPlayerEndFlg($cpuId);
			// update game status for user
			$passFlg = count($cardArray) == 0 ? true : false;
			$latestGameStatusId = $this->updateGameStatus($cpuId, $cpuEndFlg, $passFlg);
			// update game area cards for user
			$this->gameAreaManager->insertGameAreaStatus($passFlg, $latestGameStatusId, $cardArray);
		}

    /////////////////////
    // create response //
    /////////////////////
		$data = $this->createPutResponse($userId, $userCardsObj, $this->cardManager->convertCpuCards($cpuCards));

		//TODO: 更新後の次のターンのturnIdを詰める

		//$dataをJSONにして返す
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
