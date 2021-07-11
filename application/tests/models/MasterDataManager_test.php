<?php

class Masterdatamanager_test extends UnitTestCase
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
        $this->obj = $this->newModel('MasterDataManager');
    }

    public function test_getGameInfo()
    {
        $info = $this->obj->getGameInfo();
        $this->assertGreaterThan(0, count($info));
    }
}
