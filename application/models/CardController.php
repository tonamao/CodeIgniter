<?php
class CardController extends CI_Model {

	private $marks = array('club', 'diamond', 'heart', 'spade');
	private $cards['img'] = array();


	public function __construct() {
		$this->load->helper('url_helper');

		initCards();
	}

	public function initCard() {
		for ( $i = 0; $i < 54; $i++) {
			foreach ($marks as $mark) {
				array($cards['img'], $mark + '_' + $i + '.png');
			}
		}
	}

	public function get_first_hands($num_of_players) {
		
		return $cards['img'];
	}

	public function set_news() {
		$this->load->helper('url');

		$slug = url_title($this->input->post('title'), 'dash', TRUE);

		$data = array(
			'title' => $this->input->post('title'),
			'slug' => $slug,
			'text' => $this->input->post('text')
		);

		return $this->db->insert('news', $data);
	}
}