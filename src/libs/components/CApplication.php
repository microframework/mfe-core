<?php namespace mfe\core\libs\components;

/*
TODO:: Ядра, как они инициализируются внутри системы?
TODO:: Если объекты трейтов реализуются клонированием, нужно ли наследовать трейты?!
TODO:: Регистрация компонентов? она же ведь должна проходить исключительно в приложении?
*/

use mfe\core\libs\traits\application\TApplicationEngine;
use mfe\core\mfe;

/**
 * Class CApplication
 * @package mfe\core\libs\components
 */
abstract class CApplication extends CComponent
{
    const APPLICATION_NAME = 'Default application';
    const APPLICATION_TYPE = 'IHybridApplication';
    const APPLICATION_VERSION = '1.0.0';

    static public $register = [
        'TR' => [],
        'IoC' => []
    ];

    use TApplicationEngine;

    /** @var string */
    public $result;

    static public $_STATUS = 0x0000000;

    /** @var CApplication $class */
    static public $instance;

    protected $aliases;
    protected $components;
    protected $events;
    protected $eventsMap;
    protected $filesMap;

    protected $ignoreRegister = [
        'applications'
    ];

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
        $this->cloneOptions();
        $this->cloneRegister();
    }

    /**
     *
     */
    public function globalOverrideApplicationInstance()
    {
        mfe::getInstance()->registerApplication(self::$instance = $this);
    }

    /**
     *
     */
    protected function cloneRegister()
    {
        foreach (mfe::$register['TR'] as $register) {
            if (array_search($register, $this->ignoreRegister) === false) {
                $this->$register = clone mfe::getInstance()->getRegister('TR', $register);
            }
        }
    }

    /**
     *
     */
    protected function cloneOptions()
    {
        /** @var CApplication $class */
        $class = static::class;
        $class::$options = mfe::$options;
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
