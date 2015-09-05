<?php namespace mfe\core;

use mfe\core\api\applications\IApplication;
use mfe\core\api\base\IObject;
use mfe\core\api\engines\IEngine;
use mfe\core\libs\applications\TApplicationEngine;
use mfe\core\libs\components\CException;
use mfe\core\libs\handlers\CRunHandler;
use mfe\core\libs\system\PSR4Autoload;
use mfe\core\libs\traits\standard\TStandardApplication;
use mfe\core\libs\traits\standard\TStandardEngine;

if (!class_exists('\Composer\Autoload\ClassLoader')) {
    require_once __DIR__ . '/libs/system/PSR4Autoload.php';

    $loader = new PSR4Autoload;
    $loader->register();
    $loader->addNamespace('Psr\\Http\\Message\\',
        dirname(__DIR__) . '/vendor/psr/http-message/src');
    $loader->addNamespace(__NAMESPACE__, __DIR__);
}

if (file_exists($bootstrap = __DIR__ . '/bootstrap.php')) {
    /** @noinspection PhpIncludeInspection */
    include_once $bootstrap;
}

require_once __DIR__ . '/Init.php';

/**
 * MicroFramework Engine
 *
 * @author DeVinterX @ Dimitriy Kalugin <devinterx@gmail.com>
 * @link http://microframework.github.io/
 * @copyright 2014-2015 ZealoN Group, MicroFramework Group, Dimitriy Kalugin
 * @license http://microframework.github.io/license/
 * @package mfe
 * @version 1.0.7e
 */
(version_compare(phpversion(), '5.5.0', '>=')) or die('MFE has needed PHP 5.5.0+');

(defined('ROOT')) or define('ROOT', __DIR__);
(defined('MFE_TIME')) or define('MFE_TIME', microtime(true));
(defined('MFE_SERVER')) or define('MFE_SERVER', false);

/**
 * Class MfE
 *
 * This base class of logic MicroFramework, this class is Engine! This is MFE!
 * Это базовый класс реализующий двигатель MicroFramework. Это и есть MFE!
 *
 * @standards MFS-4.1, MFS-5
 * @package mfe\core
 */
class MfE implements IObject, IEngine, IApplication
{
    const ENGINE_NAME = 'MicroFramework Engine';
    const ENGINE_VERSION = '1.0.7e'; // !if mod this, mod & doc before commit!
    const ENGINE_DIR = __DIR__;

    static public $DEBUG = true;

    /** @var MfE $instance */
    static public $instance;

    use TApplicationEngine;
    use TStandardEngine;
    use TStandardApplication;

    /**
     * Constructor
     */
    protected function __construct()
    {
        //ini_set('display_errors', false);
        //self::begin();
    }

    /**
     * Singleton wrapper
     *
     * @return MfE
     * @throws CException
     */
    static public function getInstance()
    {
        return self::createEngine();
    }

    /**
     * Stop engine
     *
     * @return bool|null
     */
    final static public function stopEngine()
    {
        CRunHandler::debugHandler();
        return (bool)(self::$instance or false);
    }

    /**
     * Destructor
     *
     * @throws CException
     */
    public function __destruct()
    {
        self::end($this);
        ini_set('display_errors', true);
    }

    /**
     * Start engine
     *
     * Here the engine is registered and prepares for work all components
     *
     * @return bool
     */
    final public function startEngine()
    {
        CRunHandler::run();
        return true;
    }
}

/**
 * Auto register self in system
 *
 * @standards MFS-5.5
 */
MfE::app(new Init(__DIR__));
