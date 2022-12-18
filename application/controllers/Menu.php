<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Menu extends CI_Controller
{
    public function __construct()
  {
    parent::__construct();
        $this->load->helper('url_helper');
        $this->load->helper('html');
        $this->load->model('msGame');
  }

    public function games()
    {
        $data['game_info'] = $this->msGame->findAllGame();
        $this->load->view('menu', $data);
    }
}
