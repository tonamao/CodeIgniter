<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daifugo extends CI_Controller {

	public static $MARKS = ['club', 'diamond', 'heart', 'spade'];
	public static $INIT_CARDS = array();

	public function __construct(){
		parent::__construct();
		$this->load->helper('url_helper');
		$this->load->helper('html');
		$this->load->model('cardController');
		Daifugo::$INIT_CARDS = $this->cardController->getCardsInOrder();

	}

	public function daifugo()
	{
		$cardInOrder = $this->cardController->getCardsInOrder();
		$allRandomCardList = $this->cardController->getRandomCardsList(4, $cardInOrder);
		$data['userCardList'] = $allRandomCardList[0];
		$data['cpuCardLists'] = array();
		$numOfCpu = 3;
		for ($i = 0; $i < $numOfCpu; $i++) {
			array_push($data['cpuCardLists'], $allRandomCardList[($i + 1)]);
		}

		$data['back'] = $this->cardController->getCardBack();
		$this->load->view('daifugo/daifugo', $data);

	}

	public function init() {
		//get card from db as list and randomly push to list
		echo $this->cardController->testDb();
		//
	}
}
