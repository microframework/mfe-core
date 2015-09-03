<?php namespace mfe\core\libs\applications;

use Exception;
use mfe\core\api\applications\IStandardApplication;
use mfe\core\Init;
use mfe\core\libs\base\CComponent;
use mfe\core\libs\components\CDisplay;
use mfe\core\libs\components\CException;
use mfe\core\libs\traits\standard\TStandardApplication;
use mfe\core\libs\system\TSystemComponent;
use mfe\core\mfe;

/**
 * Class CApplication
 *
 * @package mfe\core\libs\base
 */
abstract class CApplication extends CComponent implements IStandardApplication
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
     * @throws Exception
     */
    public function __construct()
    {
        $this->init();
        MfE::getInstance()->importComponentManager($this->componentManager);
        $this->setup();
        $this->globalOverrideApplicationInstance();
    }

    /**
     * @return void
     */
    public function init()
    {
        if (null !== static::APPLICATION_DIR) {
            self::addConfigPath(static::APPLICATION_DIR, static::class);
        }
    }

    /**
     * @return void
     */
    protected function setup()
    {
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
     * @throws Exception
     */
    public function globalOverrideApplicationInstance()
    {
        $this->set('request', MfE::getInstance()->request);
        $this->set('response', MfE::getInstance()->response);
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
     * @return void
     */
    public function main(){}

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        $this->main();
        MfE::getInstance()->events->trigger('application.run');
    }
}
