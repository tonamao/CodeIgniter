<?php
require_once dirname(__FILE__).'/../util/DatabaseTester.php';
use PHPUnit\DbUnit\DataSet\YamlDataSet;

class Masterdatamanager_test extends DatabaseTester
{

    /**
     */
    protected function getDataSet()
    {
        return new YamlDataSet(
            dirname(__FILE__) . "/../" . "fixture/ms_game.yml"
        );
        // return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
        //     dirname(__FILE__) . "/../" . "fixture/ms_game.yml"
        // );
    }

    public function test_getGameInfo()
    {
        //$result = $this->obj->getGameInfo();
        $result = $this->getConnection()->getRowCount('ms_game');
        $this->assertEquals(2, $result);
    }
}
