<?php namespace mfe\core\libs\base;

/*
TODO:: Регистрация компонентов? она же ведь должна проходить исключительно в приложении?
*/

use mfe\core\Init;
use mfe\core\libs\components\CDisplay;
use mfe\core\libs\traits\application\TApplicationEngine;
use mfe\core\libs\traits\standard\TStandardApplication;
use mfe\core\libs\traits\system\TSystemComponent;
use mfe\core\mfe;

/**
 * Class CApplication
 *
 * @package mfe\core\libs\base
 */
abstract class CApplication extends CComponent
{
    const APPLICATION_NAME = 'Default application';
    const APPLICATION_TYPE = 'IHybridApplication';
    const APPLICATION_VERSION = '1.0.0';

    const APPLICATION_DIR = null;

    use TSystemComponent;
    use TApplicationEngine;
    use TStandardApplication;

    /** @var string */
    protected $result;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->init();
        $this->globalOverrideApplicationInstance();
    }

    /**
     *
     */
    public function init()
    {
        if (null !== static::APPLICATION_DIR) {
            self::addConfigPath(static::APPLICATION_DIR, static::class);
        }
    }


    /**
     * @param $_DIR
     * @param $className
     *
     * @return bool|string
     */
    public function addConfigPath($_DIR, $className)
    {
        $DIR = $_DIR . '/' . (new \ReflectionClass($className))->getShortName();
        (defined('ROOT')) or define('ROOT', $DIR);

        if (!file_exists($DIR) && is_writable($_DIR)) {
            mkdir($DIR, 0666, true);
        }

        return Init::addConfigPath($DIR, Init::DIR_TYPE_APP);
    }

    /**
     * @throws \Exception
     */
    public function globalOverrideApplicationInstance()
    {
        MfE::getInstance()->registerApplication($this);
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
        self::display($this->result . PHP_EOL, CDisplay::TYPE_PAGE);
    }
}
