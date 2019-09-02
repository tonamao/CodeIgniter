<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daifugo extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper('url_helper');
		$this->load->helper('html');
		$this->load->model('cardController');
	}

	public function menu()
	{
		$this->load->view('daifugo/menu');
	}

	public function daifugo()
	{
		$data['cards'] = $this->cardController->initCard();
		$this->load->view('daifugo/daifugo', $data);
	}
}
