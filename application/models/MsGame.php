<?php
class MsGame extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    public function findAllGame()
    {
        $query = $this->db->get('ms_game');
        return $query->result_array();
    }
}
