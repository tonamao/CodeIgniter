<?php
class RuleManager extends CI_Model {

	public function __construct() {
		$this->load->helper('url_helper');
		$this->load->database();
	}

	//TODO: create table for rules & imple rule list
	public function getRules() {
		$ruleList = array();

		return $ruleList;
	}

	//TODO: check rules
	public function checkRules($ruleList, $selectingCards) {

		return true;
	}
}
