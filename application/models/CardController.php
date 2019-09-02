<?php
class CardController extends CI_Model {

	public function __construct() {
		$this->load->helper('url_helper');
	}

	public function initCard() {
		$marks = ['club', 'diamond', 'heart', 'spade'];
		$cards = array();
		foreach ($marks as $mark) {
			for ( $i = 0; $i < 3; $i++) {
				array_push($cards, $mark.'_'.$i.'.png');
			}
		}
		return $cards;
	}

}