<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daifugo extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper('url_helper');
		$this->load->helper('html');
		$this->load->model('cardController');

	}

	public function daifugo()
	{
		//最初の手札として、プレイヤー分のcardListを取得
		$allRandomCardList = $this->cardController->getRandomCardsList(4);
		
		//最初の手札をDBに登録
		$this->cardController->insertCards(1, $allRandomCardList);
		
		//手札がidなので、img pathに変換
		$idArray = array();
		for ($i = 0; $i < count($allRandomCardList); $i++) {
			foreach ($allRandomCardList[$i] as $key => &$value) {
				$value = $this->cardController->toImgPath($value);
			}
		}

		//0番目がユーザのList、1～3がCPU1～3のList
		$data['userCardList'] = $allRandomCardList[0];
		$data['cpuCardLists'] = array();
		$numOfCpu = 3;
		for ($i = 0; $i < $numOfCpu; $i++) {
			array_push($data['cpuCardLists'], $allRandomCardList[($i + 1)]);
		}

		//裏向きのimgPath設定
		$data['back'] = $this->cardController->getCardBack();
		$this->load->view('daifugo/daifugo', $data);
	}
}
