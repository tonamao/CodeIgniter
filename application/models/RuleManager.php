<?php
class RuleManager extends CI_Model {

	public static $TRUMP;
	public static $DAIFUGO;

	public function __construct() {
		$this->load->helper('url_helper');
		
		//TODO: database.phpを直す
		RuleManager::$TRUMP = $this->load->database('default',true);
		RuleManager::$DAIFUGO = $this->load->database('daifugo', true);
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