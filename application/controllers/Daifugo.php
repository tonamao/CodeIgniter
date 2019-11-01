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
	 * 最初のカードのを表示する
	 * ⇒54枚のカードからランダムに手札を配る
	 * ⇒手札をDBに登録する
	 * ⇒手札を画面に表示する
	 */
	public function start() {
		//全プレイヤーの手札(hand)を取得
		$playerNum = 4;
		$allHandLists = $this->cardController->getFirstHandLists($playerNum);
		//手札をDBに登録
		$gameNum = 1;
		$this->cardController->insertHands($gameNum, $allHandLists);
		//手札をdataに入れる
		$data['hands'] = $allHandLists;
		$data['back'] = $this->cardController->getCardBack();
		$this->load->view('daifugo/daifugo', $data);
	}

	/**
	 * ユーザーが手札を場に出す
	 */
	public function put() {
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
