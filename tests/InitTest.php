<?php namespace mfe\core;

class InitTest extends \PHPUnit_Framework_TestCase
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

        static::assertEquals(
            $config['params']['environment'], $this->config['params']['environment']
        );
    }

    public function testAddFailConfigDir()
    {
        $result = Init::addConfigPath(__DIR__ . '/mfe.config.php', Init::DIR_TYPE_DATA);
        static::assertFalse($result);
    }

    public function testDeleteAndResetConfigDir()
    {
        $hash = Init::addConfigPath(__DIR__, Init::DIR_TYPE_DATA);
        $initObject = new Init;

        $config1 = $initObject();

        $result = $initObject::removeConfigPath(md5(mt_rand()), Init::DIR_TYPE_DATA);

        static::assertFalse($result);

        $initObject::removeConfigPath($hash, Init::DIR_TYPE_DATA);

        $initObject->reset();

        $config2 = $initObject();

        static::assertNotEquals($config1, $config2);
    }
}
