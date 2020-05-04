<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Ajax extends CI_Controller{

    public function __construct(){
        parent::__construct();
    }

    public function test(){

        // サーバ側でもってるデータ
        $array = array(
			array('framework' => 'codeigniter', 'lang' => 'php',),
            array('framework' => 'fuelphp', 'lang' => 'php',)
		);

        //postで送られてきたデータ
        $post_data = $this->input->post('number');

        //postデータをもとに$arrayからデータを抽出
        $data = $array[$post_data];
		
        //$dataをJSONにして返す
        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode($data));
    }
}
