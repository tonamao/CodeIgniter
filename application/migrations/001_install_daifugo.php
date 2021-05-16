<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Install_daifugo extends CI_Migration
{
    const COLOR_SUCCESS = 32; // green
    const COLOR_ERROR   = 31; // red

    public static $drop_table_list = [
        // master tables.
        'ms_game',
        'ms_trump_card',
        // transaction tables.
        'tb_user_playing_game',
        'tb_daifugo_matching',
        'tb_daifugo_game_manager',
        'tb_daifugo_hand',
        'tb_daifugo_user_status',
        'tb_daifugo_game_area_card',
        'tb_daifugo_result',
    ];

    public static $cleate_tables = [
        'ms_game' => [
            'fields' => [
                'id'          => ['type' => 'INT',     'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'name'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'img_path'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'description' => ['type' => 'TEXT',    'null' => true],
            ],
            'keys' => [
                'id' => true,
            ],
        ],
        'ms_trump_card' => [
            'fields' => [
                'card_id'   => ['type' => 'MEDIUMINT', 'auto_increment' => true, 'null' => false],
                'card_name' => ['type' => 'VARCHAR',   'constraint' => 20,       'null' => false],
            ],
            'keys' => [
                'card_id' => true,
            ],
        ],
        'tb_user_playing_game' => [
            'fields' => [
                'user_id' => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'game_id' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            ],
            'keys' => [
                'user_id' => true,
            ],
        ],
        'tb_daifugo_matching' => [
            'fields' => [
                'game_id'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'user_id'          => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'user_category_id' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'game_order'       => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'playing_flg'      => ['type' => 'TINYINT', 'null' => false],
            ],
            'keys' => [
                'game_id' => true,
            ],
        ],
        'tb_daifugo_game_manager' => [
            'fields' => [
                'game_status_id' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'game_id'        => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'game_turn'      => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'user_turn'      => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'turn_owner'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'pass_num'       => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'game_end_flg'   => ['type' => 'TINYINT', 'null' => false],
            ],
            'keys' => [
                'game_status_id' => true,
                'game_id'        => true,
            ],
        ],
        'tb_daifugo_hand' => [
            'fields' => [
                'game_id'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'user_id'        => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'card_id'        => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'used_flg'       => ['type' => 'TINYINT', 'null' => false],
                'strength_level' => ['type' => 'INT',     'constraint' => 11,  'null' => false],
            ],
            'keys' => [
                'game_id' => true,
                'user_id' => true,
                'card_id' => true,
            ],
        ],
        'tb_daifugo_user_status' => [
            'fields' => [
                'game_status_id' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'game_id'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'user_id'        => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'game_turn'      => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'pass_flg'       => ['type' => 'TINYINT', 'null' => false],
                'user_end_flg'   => ['type' => 'TINYINT', 'null' => false],
            ],
            'keys' => [
                'game_status_id' => true,
                'game_id' => true,
                'user_id' => true,
            ],
        ],
        'tb_daifugo_game_area_card' => [
            'fields' => [
                'game_status_id' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'card_ids'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'game_id'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'game_turn'      => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'discard_flg'    => ['type' => 'TINYINT', 'null' => false],
            ],
            'keys' => [
                'game_status_id' => true,
                'card_ids' => true,
            ],
        ],
        'tb_daifugo_result' => [
            'fields' => [
                'game_id'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
                'user_id'   => ['type' => 'INT',     'constraint' => 11,  'null' => false],
                'user_rank' => ['type' => 'INT',     'constraint' => 11,  'null' => false],
            ],
            'keys' => [
                'user_id' => true,
            ],
        ],
    ];

    public static $common_fields = [
        '`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        '`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    ];

    // アップデート処理
    public function up()
    {
        $this->cEcho('[INFO] Start Migration.', self::COLOR_SUCCESS);

        $this->load->database();

        $this->cEcho('[INFO] STEP1: Drop All Tables.', self::COLOR_SUCCESS);
        $this->_dropTablesAll(true);
        $this->cEcho('[INFO] Done.', self::COLOR_SUCCESS);

        $this->cEcho('[INFO] STEP2: Create All Tables.', self::COLOR_SUCCESS);
        $this->_cleateTablesAll();
        $this->cEcho('[INFO] Done.', self::COLOR_SUCCESS);

        $this->cEcho('[INFO] STEP3: Insert Default Data.', self::COLOR_SUCCESS);
        //$this->_cleateTablesAll();

        // FIXME なんかよういする
        /**
         * ms_game
         */
        $game_default_data = [
            'name' => '大富豪',
            'img_path' => 'assets/img/games/millionaire_image.png',
            'description' => 'トランプゲーム大富豪のサンプルです',
        ];
        $this->db->insert('ms_game', $game_default_data);

        /**
         * ms_trump_card
         */
        //Insert ms trump card data
        $mark = ['club', 'diamond', 'heart', 'spade'];
        for ($i = 0; $i < count($mark); $i++) {
            for ($j = 0; $j < 13; $j++) {
                $ms_data = [
                    'card_id'   => (13 * $i) + ($j + 1),
                    'card_name' => $mark[$i] . '_' . ($j + 1)
                ];
                $this->db->insert('ms_trump_card', $ms_data);
            }
        }

        //Insert 2 jokers
        for ($i = 0; $i < 2; $i++) {
            $joker_data = [
                'card_id'   => (53 + $i),
                'card_name' => 'joker_' . ($i + 1)
            ];
            $this->db->insert('ms_trump_card', $joker_data);
        }

        //Insert card back
        $back_data = [
            'card_id'   => 55,
            'card_name' => 'back'
        ];
        $this->db->insert('ms_trump_card', $back_data);

        /**
         * user_playing_game
         */
        $test_user = [
            'user_id' => '9000000', // idなので数値にしとく
            'game_id' => 'DFG0' // なにかのhash値でいいかも
        ];
        $this->db->insert('tb_user_playing_game', $test_user);
        $this->cEcho('[INFO] Done.', self::COLOR_SUCCESS);

        /**
         * daifugo_matching
         */
        // Drop table 'daifugo_matching' if it exists
        //$this->dbforge->drop_table('daifugo_matching', TRUE);

        // Table structure for table 'daifugo_matching'
        //$this->dbforge->add_field([
        //    'game_id' => [
        //        'type'       => 'VARCHAR',
        //        'constraint' => '255',
        //        'null'       => FALSE
        //    ],
        //    'user_id' => [
        //        'type'       => 'VARCHAR',
        //        'constraint' => '255',
        //        'null'       => FALSE
        //    ],
        //    'user_category_id' => [
        //        'type'       => 'VARCHAR',
        //        'constraint' => '255',
        //        'null'       => FALSE
        //    ],
        //    'game_order' => [
        //        'type'       => 'INT',
        //        'null'       => FALSE
        //    ],
        //    'playing_flg' => [
        //        'type'       => 'TINYINT',
        //        'null'       => FALSE
        //    ]
        //]);

        ////Create table 'daifugo_matching'
        //if ($this->dbforge->create_table('daifugo_matching')) {
        //    echo 'Create table daifugo_matching Success!' . PHP_EOL;
        //}


        /**
         * daifugo_game_manager
         */

        // Table structure for table 'daifugo_game_manager'
        //$this->dbforge->add_field([
        //    'game_status_id' => [
        //        'type'       => 'VARCHAR',
        //        'constraint' => '255',
        //        'null'       => FALSE
        //    ],
        //    'game_id' => [
        //        'type'       => 'VARCHAR',
        //        'constraint' => '255',
        //        'null'       => FALSE
        //    ],
        //    'game_turn' => [
        //        'type'       => 'INT'
        //    ],
        //    'user_turn' => [
        //        'type'       => 'VARCHAR',
        //        'constraint' => '255'
        //    ],
        //    'turn_owner' => [
        //        'type'       => 'VARCHAR',
        //        'constraint' => '255'
        //    ],
        //    'pass_num' => [
        //        'type'       => 'INT'
        //    ],
        //    'game_end_flg' => [
        //        'type'       => 'TINYINT',
        //        'null'       => FALSE
        //    ],
        //    'insert_time' => [
        //        'type'       => 'TIMESTAMP',
        //        'null'       => FALSE
        //    ]
        //]);
        //$this->dbforge->add_key('game_status_id', TRUE);
        //$this->dbforge->add_key('game_id', TRUE);

        ////Create table 'daifugo_game_manager'
        //if ($this->dbforge->create_table('daifugo_game_manager')) {
        //    echo 'Create table daifugo_game_manager Success!' . PHP_EOL;
        //}


        /**
         * daifugo_hand
         */
        // Drop table 'daifugo_hand' if it exists
        //$this->dbforge->drop_table('daifugo_hand', TRUE);

        // Table structure for table 'daifugo_hand'
        //$this->dbforge->add_field([
        //    'game_id' => [
        //        'type'       => 'VARCHAR',
        //        'constraint' => '255',
        //        'null'       => FALSE
        //    ],
        //    'user_id' => [
        //        'type'       => 'VARCHAR',
        //        'constraint' => '255',
        //        'null'       => FALSE
        //    ],
        //    'card_id' => [
        //        'type'       => 'INT',
        //        'null'       => FALSE
        //    ],
        //    'used_flg' => [
        //        'type'       => 'TINYINT',
        //        'null'       => FALSE
        //    ],
        //    'strength_level' => [
        //        'type'       => 'INT',
        //        'null'       => FALSE
        //    ]
        //]);
        //$this->dbforge->add_key('game_id', TRUE);
        //$this->dbforge->add_key('user_id', TRUE);
        //$this->dbforge->add_key('card_id', TRUE);

        ////Create table 'daifugo_hand'
        //if ($this->dbforge->create_table('daifugo_hand')) {
        //    echo 'Create table daifugo_hand Success!' . PHP_EOL;
        //}


        /**
         * daifugo_user_status
         */
        // Drop table 'daifugo_user_status' if it exists
        //$this->dbforge->drop_table('daifugo_user_status', TRUE);

        // Table structure for table 'daifugo_user_status'
        // $this->dbforge->add_field([
        //     'game_status_id' => [
        //         'type'       => 'VARCHAR',
        //         'constraint' => '255',
        //         'null'       => FALSE
        //     ],
        //     'game_id' => [
        //         'type'       => 'VARCHAR',
        //         'constraint' => '255',
        //         'null'       => FALSE
        //     ],
        //     'game_turn' => [
        //         'type'       => 'INT'
        //     ],
        //     'user_id' => [
        //         'type'       => 'VARCHAR',
        //         'constraint' => '255',
        //         'null'       => FALSE
        //     ],
        //     'pass_flg' => [
        //         'type'       => 'TINYINT',
        //         'null'       => FALSE
        //     ],
        //     'user_end_flg' => [
        //         'type'       => 'TINYINT',
        //         'null'       => FALSE
        //     ]
        // ]);
        // $this->dbforge->add_key('game_status_id', TRUE);
        // $this->dbforge->add_key('game_id', TRUE);
        // $this->dbforge->add_key('user_id', TRUE);

        // //Create table 'daifugo_user_status'
        // if ($this->dbforge->create_table('daifugo_user_status')) {
        //     echo 'Create table daifugo_user_status Success!' . PHP_EOL;
        // }


        /**
         * daifugo_game_area_card
         */
        // Drop table 'daifugo_game_area_card' if it exists
        //$this->dbforge->drop_table('daifugo_game_area_card', TRUE);

        // Table structure for table 'daifugo_game_area_card'
        // $this->dbforge->add_field([
        //     'game_status_id' => [
        //         'type'       => 'VARCHAR',
        //         'constraint' => '255',
        //         'null'       => FALSE
        //     ],
        //     'game_id' => [
        //         'type'       => 'VARCHAR',
        //         'constraint' => '255',
        //         'null'       => FALSE
        //     ],
        //     'game_turn' => [
        //         'type'       => 'INT'
        //     ],
        //     'card_ids' => [
        //         'type'       => 'VARCHAR',
        //         'constraint' => '255'
        //     ],
        //     'discard_flg' => [
        //         'type'       => 'TINYINT',
        //         'null'       => FALSE
        //     ]
        // ]);
        // $this->dbforge->add_key('game_status_id', TRUE);
        // $this->dbforge->add_key('card_ids', TRUE);

        // //Create table 'daifugo_game_area_card'
        // if ($this->dbforge->create_table('daifugo_game_area_card')) {
        //     echo 'Create table daifugo_game_area_card Success!' . PHP_EOL;
        // }


        /**
         * daifugo_result
         */
        // Drop table 'daifugo_result' if it exists
        //$this->dbforge->drop_table('daifugo_result', TRUE);

        // Table structure for table 'daifugo_result'
        //$this->dbforge->add_field([
        //    'game_id' => [
        //        'type'       => 'VARCHAR',
        //        'constraint' => '255',
        //        'null'       => FALSE
        //    ],
        //    'user_id' => [
        //        'type'       => 'VARCHAR',
        //        'constraint' => '255',
        //        'null'       => FALSE
        //    ],
        //    'user_rank' => [
        //        'type'       => 'INT'
        //    ]
        //]);
        //$this->dbforge->add_key('user_id', TRUE);

        ////Create table 'daifugo_result'
        //if ($this->dbforge->create_table('daifugo_result')) {
        //    echo 'Create table daifugo_result Success!' . PHP_EOL;
        //}

        $this->cEcho('[INFO] Finished Migration.', self::COLOR_SUCCESS);
    }

    // ロールバック処理
    public function down()
    {
    }

    public function cEcho($message, $color)
    {
        $string = sprintf("\033[%dm%s\033[m", $color, $message);
        echo $string . PHP_EOL;
    }

    // =====================================
    // private functions
    // =====================================

    private function _dropTablesAll($is_exists = false)
    {
        foreach (self::$drop_table_list as $table) {
            $this->dbforge->drop_table($table, $is_exists);
            echo "[INFO] Drop Table {$table}." . PHP_EOL;
        }
    }

    private function _cleateTablesAll()
    {
        foreach (self::$cleate_tables as $table => $info) {
            if (!isset($info['fields'])) {
                continue;
            }
            $fields = array_merge($info['fields'], self::$common_fields);
            $this->dbforge->add_field($fields);

            if (!isset($info['keys']) || empty($info['keys'])) {
                continue;
            }
            foreach ($info['keys'] as $key => $primary_key) {
                $this->dbforge->add_key($key, $primary_key);
            }

            $this->dbforge->create_table($table);
            echo "[INFO] Create Table {$table}." . PHP_EOL;
        }
    }
}
