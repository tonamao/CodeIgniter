<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Install_daifugo extends CI_Migration {
	// アップデート処理
	public function up() {
		$this->load->database();

		/**
		 * ms_trump_card
		 */
		// Drop table 'ms_trump_card' if it exists
		$this->dbforge->drop_table('ms_trump_card', TRUE);

		// Table structure for table 'ms_trump_card'
		$this->dbforge->add_field([
			'card_id' => [
				'type' => 'MEDIUMINT',
				'auto_increment' => TRUE,
				'null' => FALSE,
			],
			'card_name' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
				'null' => FALSE,
			],
		]);
		$this->dbforge->add_key('card_id', TRUE);

		//Create table 'ms_trump_card'
		if ($this->dbforge->create_table('ms_trump_card')) {
			echo 'Create table ms_trump_card Success!';
		}

		//Insert ms trump card data
		$mark = ['club', 'diamond', 'heart', 'spade'];
		for ($i = 0; $i < count($mark); $i++) {
			for ($j = 0; $j < 13; $j++) {
				$ms_data = [
					'card_id' => (13 * $i) + ($j + 1),
					'card_name' => $mark[$i] . '_' . ($j + 1),
				];
				$this->db->insert('ms_trump_card', $ms_data);
			}
		}

		//Insert 2 jokers
		for ($i = 0; $i < 2; $i++) {
			$joker_data = [
				'card_id' => (53 + $i),
				'card_name' => 'joker_' . ($i + 1),
			];
			$this->db->insert('ms_trump_card', $joker_data);
		}

		//Insert card back
		$back_data = [
			'card_id' => 55,
			'card_name' => 'back',
		];
		$this->db->insert('ms_trump_card', $back_data);

		/**
		 * ms_game
		 */
		// Drop table 'ms_game' if it exists
		$this->dbforge->drop_table('ms_game', TRUE);

		// Table structure for table 'ms_game'
		$this->dbforge->add_field([
			'id' => [
				'type' => 'MEDIUMINT',
				'auto_increment' => TRUE,
				'null' => FALSE,
			],
			'display_name' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				'null' => FALSE,
			],
			'img_path' => [
				'type' => 'VARCHAR',
				'constraint' => '50',
				'null' => FALSE,
			],
			'description' => [
				'type' => 'VARCHAR',
				'constraint' => '200',
				'null' => FALSE,
			],
		]);
		$this->dbforge->add_key('id', TRUE);

		//Create table 'ms_game'
		if ($this->dbforge->create_table('ms_game')) {
			echo 'Create table ms_game Success!';
		}

		$ms_game = [
			'id' => '1',
			'display_name' => 'DAIFUGO',
			'img_path' => 'DAIFUGO',
			'description' => '手持ちのカードをルールに従って場に出して、大富豪を目指そう！',
		];
		$this->db->insert('ms_game', $ms_game);

		/**
		 * user_playing_game
		// Drop table 'user_playing_game' if it exists
		$this->dbforge->drop_table('user_playing_game', TRUE);

		// Table structure for table 'user_playing_game'
		$this->dbforge->add_field([
			'user_id' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'playing_game_id' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
		]);

		//Create table 'user_playing_game'
		if ($this->dbforge->create_table('user_playing_game')) {
			echo 'Create table user_playing_game Success!';
		}

		//TODO: ユーザーを作るときにINSERTするようにする(INSERT TEST DATA)
		$test_user = [
			'user_id' => 'user0',
			'playing_game_id' => 'DFG0',
		];
		$this->db->insert('user_playing_game', $test_user);
    */

		/**
		 * daifugo_matching
		 */
		// Drop table 'daifugo_matching' if it exists
		$this->dbforge->drop_table('daifugo_matching', TRUE);

		// Table structure for table 'daifugo_matching'
		$this->dbforge->add_field([
			'game_id' => [
				'type' => 'MEDIUMINT',
				'null' => FALSE,
			],
			'player_1' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
				'null' => FALSE,
			],
			'player_2' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
				'null' => FALSE,
			],
			'player_3' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
				'null' => FALSE,
			],
			'player_4' => [
				'type' => 'VARCHAR',
				'constraint' => '20',
				'null' => FALSE,
			],
			'game_order' => [
				'type' => 'INT',
				'null' => FALSE,
			],
			'rule_ids' => [
				'type' => 'VARCHAR',
				'constraint' => '80',
			],
			'playing_flg' => [
				'type' => 'TINYINT',
				'null' => FALSE,
			],
		]);

		/**
		 * INSERT INTO daifugo_matching VALUES ('1', 'user_0', 'cpu, 'cpu', 'cpu',....)
		 */

		//Create table 'daifugo_matching'
		if ($this->dbforge->create_table('daifugo_matching')) {
			echo 'Create table daifugo_matching Success!';
		}

		/**
		 * daifugo_turn_status
		 */
		// Drop table 'daifugo_turn_status' if it exists
		$this->dbforge->drop_table('daifugo_turn_status', TRUE);

		// Table structure for table 'daifugo_turn_status'
		$this->dbforge->add_field([
			'game_status_id' => [
				'type' => 'MEDIUMINT',
				'auto_increment' => TRUE,
				'null' => FALSE,
			],
			'game_id' => [
				'type' => 'MEDIUMINT',
				'null' => FALSE,
			],
			'game_turn' => [
				'type' => 'INT',
			],
			'turn_user' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'turn_owner' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'pass_num' => [
				'type' => 'INT',
			],
			'turn_end_flg' => [
				'type' => 'TINYINT',
				'null' => FALSE,
			],
		  'updated_at DATETIME default CURRENT_TIMESTAMP',
    ]);
		$this->dbforge->add_key('game_status_id', TRUE);
		$this->dbforge->add_key('game_id', TRUE);

		//Create table 'daifugo_turn_status'
		if ($this->dbforge->create_table('daifugo_turn_status')) {
			echo 'Create table daifugo_turn_status Success!';
		}

		/**
		 * daifugo_hand
		 */
		// Drop table 'daifugo_hand' if it exists
		$this->dbforge->drop_table('daifugo_hand', TRUE);

		// Table structure for table 'daifugo_hand'
		$this->dbforge->add_field([
			'game_id' => [
				'type' => 'MEDIUMINT',
				'null' => FALSE,
			],
			'user_id' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => FALSE,
			],
			'card_id' => [
				'type' => 'INT',
				'null' => FALSE,
			],
			'used_flg' => [
				'type' => 'TINYINT',
				'null' => FALSE,
			],
			'strength_level' => [
				'type' => 'INT',
				'null' => FALSE,
			],
		]);
		$this->dbforge->add_key('game_id', TRUE);
		$this->dbforge->add_key('user_id', TRUE);
		$this->dbforge->add_key('card_id', TRUE);

		//Create table 'daifugo_hand'
		if ($this->dbforge->create_table('daifugo_hand')) {
			echo 'Create table daifugo_hand Success!';
    }

		/**
		 * daifugo_game_status
		 */
		// Drop table 'daifugo_game_status' if it exists
		$this->dbforge->drop_table('daifugo_game_status', TRUE);

		// Table structure for table 'daifugo_game_status'
		$this->dbforge->add_field([
			'game_id' => [
				'type' => 'MEDIUMINT',
				'null' => FALSE,
			],
			'game_end_flg' => [
				'type' => 'TINYINT',
				'null' => FALSE,
			],
		  'updated_at DATETIME default CURRENT_TIMESTAMP',
		]);

		//Create table 'daifugo_game_status'
		if ($this->dbforge->create_table('daifugo_game_status')) {
			echo 'Create table daifugo_game_status Success!';
		}

		/**
		 * daifugo_user_status
		 */
		// Drop table 'daifugo_user_status' if it exists
		$this->dbforge->drop_table('daifugo_user_status', TRUE);

		// Table structure for table 'daifugo_user_status'
		$this->dbforge->add_field([
			'game_status_id' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => FALSE,
			],
			'game_id' => [
				'type' => 'MEDIUMINT',
				'null' => FALSE,
			],
			'game_turn' => [
				'type' => 'INT',
			],
			'user_id' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => FALSE,
			],
			'pass_flg' => [
				'type' => 'TINYINT',
				'null' => FALSE,
			],
			'user_end_flg' => [
				'type' => 'TINYINT',
				'null' => FALSE,
			],
		]);
		$this->dbforge->add_key('game_status_id', TRUE);
		$this->dbforge->add_key('game_id', TRUE);
		$this->dbforge->add_key('user_id', TRUE);

		//Create table 'daifugo_user_status'
		if ($this->dbforge->create_table('daifugo_user_status')) {
			echo 'Create table daifugo_user_status Success!';
		}

		/**
		 * daifugo_game_area_card
		 */
		// Drop table 'daifugo_game_area_card' if it exists
		$this->dbforge->drop_table('daifugo_game_area_card', TRUE);

		// Table structure for table 'daifugo_game_area_card'
		$this->dbforge->add_field([
			'game_status_id' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => FALSE,
			],
			'game_id' => [
				'type' => 'MEDIUMINT',
				'null' => FALSE,
			],
			'game_turn' => [
				'type' => 'INT',
			],
			'card_ids' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
			],
			'discard_flg' => [
				'type' => 'TINYINT',
				'null' => FALSE,
			],
		]);
		$this->dbforge->add_key('game_status_id', TRUE);
		$this->dbforge->add_key('card_ids', TRUE);

		//Create table 'daifugo_game_area_card'
		if ($this->dbforge->create_table('daifugo_game_area_card')) {
			echo 'Create table daifugo_game_area_card Success!';
		}

		/**
		 * daifugo_result
		 */
		// Drop table 'daifugo_result' if it exists
		$this->dbforge->drop_table('daifugo_result', TRUE);

		// Table structure for table 'daifugo_result'
		$this->dbforge->add_field([
			'user_id' => [
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => FALSE,
			],
			'game_id' => [
				'type' => 'MEDIUMINT',
				'null' => FALSE,
			],
			'user_rank' => [
				'type' => 'INT',
			],
		]);
		$this->dbforge->add_key('user_id', TRUE);

		//Create table 'daifugo_result'
		if ($this->dbforge->create_table('daifugo_result')) {
			echo 'Create table daifugo_result Success!';
		}

	}

	// ロールバック処理
	public function down() {
	}

}
