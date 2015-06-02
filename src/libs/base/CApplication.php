<?php namespace mfe\core\libs\base;

/*
TODO:: Ядра, как они инициализируются внутри системы?
TODO:: Если объекты трейтов реализуются клонированием, нужно ли наследовать трейты?!
TODO:: Регистрация компонентов? она же ведь должна проходить исключительно в приложении?
*/

use mfe\core\Init;
use mfe\core\libs\components\CDisplay;
use mfe\core\deprecated\TApplicationEngine;
use mfe\core\libs\traits\system\TSystemComponent;
use mfe\core\mfe;

/**
 * Class CApplication
 * @package mfe\core\libs\base
 */
abstract class CApplication extends CComponent
{
    const APPLICATION_NAME = 'Default application';
    const APPLICATION_TYPE = 'IHybridApplication';
    const APPLICATION_VERSION = '1.0.0';

    use TSystemComponent;
    use TApplicationEngine;

    /** @var string */
    public $result;

    static public $_STATUS = 0x0000000;

    /** @var CApplication $class */
    static public $instance;

    /**
     *
     */
    public function __construct()
    {
        $this->init();

        if (is_null(self::$instance)) {
            $this->globalOverrideApplicationInstance();
        }
    }

    /**
     *
     */
    public function init()
    {
        //$this->cloneOptions();
        //$this->cloneRegister();
    }


    /**
     * @param $_DIR
     * @param $className
     * @return bool|string
     */
    public function addConfigPath($_DIR, $className)
    {
        $DIR = $_DIR . '/' . (new \ReflectionClass($className))->getShortName();
        (defined('ROOT')) or define('ROOT', $DIR);

        if (!file_exists($DIR)) {
            if (is_writable($_DIR)) {
                mkdir($DIR, 0666, true);
            }
        }

        return Init::addConfigPath($DIR, Init::DIR_TYPE_APP);
    }

    /**
     *
     */
    public function globalOverrideApplicationInstance()
    {
        MfE::getInstance()->registerApplication(self::$instance = $this);
    }

    /**
     *
     */
    protected function cloneRegister()
    {

    }

    /**
     *
     */
    protected function cloneOptions()
    {

    }

    /**
     * @return CApplication
     */
    static public function getInstance()
    {
        if (is_null(self::$instance)) {
            /** @var CApplication $class */
            $class = static::class;
            new $class();
        }
        return self::$instance;
    }

    /**
     * @return string
     */
    static public function getMetaInfo()
    {
        /** @var CApplication $class */
        $class = static::class;

        return json_encode([
            'name' => $class::APPLICATION_NAME,
            'type' => $class::APPLICATION_TYPE,
            'version' => $class::APPLICATION_VERSION
        ]);
    }

    /**
     *
     */
    public function run()
    {
        CDisplay::display($this->result);
    }

    /**
     * @return CApplication
     */
    static public function app()
    {
        return self::getInstance();
    }
}
