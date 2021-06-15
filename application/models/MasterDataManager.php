<?php
class MasterDataManager extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    /**
     * get game info data
     */
    public function getGameInfo()
    {
        $query = $this->db->get('ms_game');
        return $query->result_array();
    }
}
