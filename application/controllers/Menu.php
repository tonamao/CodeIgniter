<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Menu extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url_helper');
        $this->load->helper('html');
        $this->load->model('masterDataManager');
    }

    // FIXME 画面上で記載されていたらリファクタする
    //public function games()
    public function index()
    {
        //TODO: ゲーム情報をDBから取ってくる
        $data['game_info'] = $this->masterDataManager->getGameInfo();
        $this->load->view('menu', $data);
    }
}
