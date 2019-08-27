<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daifugo extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helper('url_helper');
		$this->load->helper('html');
	}

	public function menu()
	{
		$data['card'] = $this->cardcontroller->init();

		$this->load->view('daifugo/menu', $data);
	}

	public function index()
	{
		$this->load->view('daifugo/daifugo', $data);
	}
}
