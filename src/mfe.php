<?php namespace mfe\core;

use mfe\core\libs\system\PSR4Autoload;

use mfe\core\libs\interfaces\IEventsManager;

use mfe\core\libs\traits\application\TApplicationEngine;

use mfe\core\libs\traits\standard\TStandardApplication;
use mfe\core\libs\traits\standard\TStandardComponents;
use mfe\core\libs\traits\standard\TStandardEngine;
use mfe\core\libs\traits\standard\TStandardEvents;
use mfe\core\libs\traits\standard\TStandardLoader;

use mfe\core\libs\components\CException;
use mfe\core\libs\handlers\CRunHandler;

if (!class_exists('\Composer\Autoload\ClassLoader')) {
    require_once __DIR__ . '/libs/system/PSR4Autoload.php';

    $loader = new PSR4Autoload;
    $loader->register();
    $loader->addNamespace(__NAMESPACE__, __DIR__);
}

/**
 * MicroFramework Engine
 *
 * @author DeVinterX @ Dimitriy Kalugin <devinterx@gmail.com>
 * @link http://microframework.github.io/engine/
 * @copyright 2014 ZealoN Group, MicroFramework Group, Dimitriy Kalugin
 * @license http://microframework.github.io/license/
 * @package mfe
 * @version 1.0.7b
 */
(version_compare(phpversion(), '5.5.0', '>=')) or die('MFE has needed PHP 5.5.0+');

(defined('MFE_AUTOLOAD')) or define('MFE_AUTOLOAD', true);
(defined('MFE_TIME')) or define('MFE_TIME', microtime(true));

/**
 * Class mfe
 *
 * This base class of logic MicroFramework, this class is Engine! This is MFE!
 * Это базовый класс реализующий двигатель MicroFramework. Это и есть MFE!
 *
 * @standards MFS-4.1, MFS-5
 * @package mfe\core
 */
class mfe implements IEventsManager
{
    const ENGINE_NAME = 'MicroFramework Engine';
    const ENGINE_VERSION = '1.0.7b'; // !if mod this, mod & doc before commit!

    static public $DEBUG = false;

    /** @var mfe $instance */
    static public $instance;
    static public $register = [
        'TR' => [],
        'IoC' => []
    ];

    use TApplicationEngine;

    use TStandardEngine;
    use TStandardComponents;
    use TStandardEvents;
    use TStandardLoader;
    use TStandardApplication;

    /**
     * Constructor
     */
    protected function __construct()
    {
        @ini_set('display_errors', false);

        set_error_handler(['mfe\core\libs\handlers\CRunHandler', 'errorHandler'], E_ALL);
        set_exception_handler(['mfe\core\libs\handlers\CRunHandler', 'exceptionHandler']);
        register_shutdown_function(['mfe\core\mfe', 'stopEngine']);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        self::end();
    }

    /**
     * Start engine
     *
     * Here the engine is registered and prepares for work all components
     *
     * @return bool
     * @throws CException
     */
    final public function startEngine()
    {
        //TODO:: Where from phar archive register specific paths
        if (self::option('MFE_PHAR_INIT')) {

        }

        $RUN = array_reverse(explode('/', $_SERVER['SCRIPT_NAME']))[0];
        $REAL_PATH = dirname(realpath($RUN));

        self::registerAlias('@engine', __DIR__);
        if (__DIR__ !== $REAL_PATH && file_exists($REAL_PATH . '/') && is_dir($REAL_PATH . '/'))
            self::registerAlias('@engine', $REAL_PATH . '/');
        self::registerAlias('@libs', 'libs');
        self::registerAlias('@core', 'core');

        // Load main core files by map file!
        !(self::loadMapFile('@core.core')) or self::loadMap('core');

        try {
            return self::trigger('engine.start');
        } catch (CException $e) {
            mfe::stop(0x00000E1);
        }

        return false;
    }

    /**
     * Stop engine
     *
     * @return bool|null
     */
    final static public function stopEngine()
    {
        if (!is_null(error_get_last()) && self::$_STATUS !== 0x00000E0) CRunHandler::FatalErrorHandler();
        if (isset(self::$instance) || is_null(self::$instance)) return CRunHandler::DebugHandler();
        self::trigger('engine.stop');
        return !(bool) (self::$instance = null);
    }
}

/**
 * Auto register self in system
 * @standards MFS-5.5
 */
(!mfe::option('MFE_AUTOLOAD')) or mfe::app();
