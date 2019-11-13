<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daifugo extends CI_Controller {
	public static $usedId = 1;

	public function __construct(){
		parent::__construct();
		$this->load->helper('url_helper');
		$this->load->helper('html');
		$this->load->model('cardController');
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
		//TODO: insert daifugo_matching(insert num of player records)
		// $this->cardController->insertDaifugoMatching();//TODO: insertDaifugoMatching()

		//get all player's hands
		$playerNum = 4;//$this->cardController->getNumOfPlayer();//TODO: getNumOfPlayer()
		$data['all_hands'] = $this->cardController->getFirstHandsLists($playerNum);
		$data['back'] = $this->cardController->getCardBack();

		//TODO: check player's class & exchange cards -> update hand DB

		$this->load->view('daifugo/daifugo', $data);
	}

	/**
	 * 
	 */
	public function put() {
		//TODO: check card according to rules
		//true insert
		//false view

		//TODO: insert DB
		//update hand

		//insert daifugo_game_manager

		//insert user_status

		//insert game_history

		//TODO: check user end

		//TODO: check game end 

		//TODO: if game is end, update DB
		//update matching

		//insert game_result

		//TODO: view

	}

	/**
	 * 
	 */
	public function pass() {
		//TODO: insert DB
		//insert game_manager

		//insert user_status

		//insert game_history(null?)

		//TODO: view
	}


////////////////////////////////////////////////////////////////////////////////////

	/**
	 * ユーザーが手札を場に出す
	 */
	public function oldput() {
		$selectingCards = $this->input->post('hidden-put');
		$gameNum = 1;
		$playerNum = 4;
		//出そうとしてるカードがruleに合ってるかチェックする
		if ($this->cardController->checkCards($selectingCards)) {
			//used_flgを有効"TRUE"にして更新する
			$this->cardController->useCard($gameNum, $selectingCards, Daifugo::$usedId++);
		}
		$data['hands'] = $this->cardController->getHandLists($gameNum, $playerNum);
		$data['back'] = $this->cardController->getCardBack();

		//場のカードを表示
		$data['used'] = $this->cardController->getUsedCard($gameNum);

		//再表示
		$this->load->view('daifugo/daifugo', $data);
	}
}
