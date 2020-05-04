<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Menu extends CI_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->helper('url_helper');
		$this->load->helper('html');
		$this->load->model('masterDataManager');
	}
	public function games()
	{
		//TODO: ゲーム情報をDBから取ってくる
		$data['gameInfo'] = $this->masterDataManager->getGameInfo();
		$this->load->view('menu', $data);
	}
}
