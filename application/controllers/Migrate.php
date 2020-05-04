<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller {

	public function __construct(){
		parent::__construct();
	}

    public function latest()
    {
        $this->load->library('migration');
        if ($this->migration->latest() === FALSE)
        {
            show_error($this->migration->error_string());
        }
    }
}

