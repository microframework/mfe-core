<?php namespace mfe\core;

use PHPUnit_Framework_TestCase;

class InitTest extends PHPUnit_Framework_TestCase
{
    private $config;

    public function setUp()
    {
        $this->config = include(__DIR__ . '/mfe.config.php');
    }

    public function testAddCustomConfig()
    {
        Init::addConfigPath(__DIR__, Init::DIR_TYPE_DATA);
        $initObject = new Init;

        $config = $initObject();

        $this->assertEquals(
            $config['params']['environment'], $this->config['params']['environment']
        );
    }

    public function testAddFailConfigDir()
    {
        $result = Init::addConfigPath(__DIR__ . '/mfe.config.php', Init::DIR_TYPE_DATA);
        $this->assertFalse($result);
    }

    public function testDeleteAndResetConfigDir()
    {
        $hash = Init::addConfigPath(__DIR__, Init::DIR_TYPE_DATA);
        $initObject = new Init;

        $config1 = $initObject();

        $result = $initObject::removeConfigPath(md5(rand()), Init::DIR_TYPE_DATA);

        $this->assertFalse($result);

        $initObject::removeConfigPath($hash, Init::DIR_TYPE_DATA);

        $initObject->reset();

        $config2 = $initObject();

        $this->assertNotEquals($config1, $config2);
    }
}
