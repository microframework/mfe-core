<?php namespace mfe\core\libs\applications;

use Exception;
use mfe\core\api\applications\IApplication;
use mfe\core\api\applications\IStandardApplication;
use mfe\core\Init;
use mfe\core\libs\base\CComponent;
use mfe\core\libs\components\CException;
use mfe\core\libs\http\CResponse;
use mfe\core\libs\system\TSystemComponent;
use mfe\core\libs\traits\standard\TStandardApplication;
use mfe\core\mfe;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CApplication
 *
 * @package mfe\core\libs\base
 */
abstract class CApplication extends CComponent implements IStandardApplication, IApplication
{
    const APPLICATION_NAME = 'Default application';
    const APPLICATION_TYPE = 'IHybridApplication';
    const APPLICATION_VERSION = '1.0.0';

    const APPLICATION_DIR = null;

    use TSystemComponent;
    use TApplicationEngine;
    use TStandardApplication;

    /** @var ServerRequestInterface */
    public $request;

    /** @var ResponseInterface */
    public $response;

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
        $this->response = new CResponse();
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
     * @return void
     */
    public function setup()
    {
    }

    /**
     * @throws Exception
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
     * @return void
     * @throws CException
     */
    public function run()
    {
        $this->main();
        MfE::getInstance()->events->trigger('application.run');
    }

    /**
     * @return void
     */
    public function main()
    {
    }
}
