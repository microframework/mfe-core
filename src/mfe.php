<?php namespace mfe\core;

require_once __DIR__ . '/libs/autoload.php';

use mfe\core\libs\PSR4Autoload;

use mfe\core\libs\interfaces\IEventsManager;

use mfe\core\libs\traits\TStandardApplication;
use mfe\core\libs\traits\TStandardComponents;
use mfe\core\libs\traits\TStandardEngine;
use mfe\core\libs\traits\TStandardEvents;
use mfe\core\libs\traits\TStandardLoader;

use mfe\core\libs\components\CDebug;
use mfe\core\libs\components\CException;
use mfe\core\libs\components\CRunHandler;

$loader = new PSR4Autoload;
$loader->register();
$loader->addNamespace(__NAMESPACE__, __DIR__);

/**
 * MicroFramework Engine
 *
 * @author DeVinterX @ Dimitriy Kalugin <devinterx@gmail.com>
 * @link http://microframework.github.io/engine/
 * @copyright 2014 ZealoN Group, MicroFramework Group, Dimitriy Kalugin
 * @license http://microframework.github.io/license/
 * @package mfe
 * @version 1.0.6
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
final class mfe implements IEventsManager
{
    const ENGINE_NAME = 'MicroFramework Engine';
    const ENGINE_VERSION = '1.0.6'; // !if mod this, mod & doc before commit!

    static public $DEBUG = false;

    /** @var mfe $instance */
    static public $instance;
    static public $options = [];
    static public $register = [];

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
        //@ini_set('display_errors', false);

        //set_error_handler(['mfe\core\libs\components\CRunHandler', 'errorHandler'], E_ALL);
        //set_exception_handler(['mfe\core\libs\components\CRunHandler', 'exceptionHandler']);
        //register_shutdown_function(['mfe\core\mfe', 'stopEngine']);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $time = round(microtime(true) - MFE_TIME, 3);
        file_put_contents('php://stdout', PHP_EOL .
            ((!self::$_STATUS) ? 'Done: ' : 'Error: ' . CDebug::$_CODE[self::$_STATUS] . ', at ') .
            (($time >= 0.001) ? $time : '0.001') . ' ms' . PHP_EOL);
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
        //mfe::dependence('CDisplay');

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

        //Load main libs & core files by map file!
        //if (self::loadMapFile('@libs.libs')) self::loadMap('libs'); //TODO:: Это устарело, удалить
        if (self::loadMapFile('@core.core')) self::loadMap('core');

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
        self::$instance = null;
        return true;
    }
}

/**
 * Auto register self in system
 * @standards MFS-5.5
 */
(!mfe::option('MFE_AUTOLOAD')) or mfe::app();
