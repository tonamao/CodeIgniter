<?php

class MsGame_test extends UnitTestCase
{
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();
        $CI =& get_instance();
        $CI->load->library('Seeder');
        $CI->seeder->call('MsGameSeeder');
    }

    public  function setUp() : void
    {
        $this->obj = $this->newModel('MsGame');
    }

    public function test_findAllGame()
    {
        $info = $this->obj->findAllGame();
        $this->assertGreaterThan(0, count($info));
    }
}
