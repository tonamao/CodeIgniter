<?php
class MsGameSeeder extends Seeder {

    private $table = 'ms_game';

    public function run()
    {
        $this->db->truncate($this->table);

        $data = [
            'display_name' => "テストゲーム１",
            'img_path' => "assets/img/games/millionaire_image.png",
            'description' => "テスト用ゲーム１",
        ];
        $this->db->insert($this->table, $data);

        // $data = [
        //     'id' => 2,
        //     'name' => 'CD',
        // ];
        // $this->db->insert($this->table, $data);
    }

}
