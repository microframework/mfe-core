<?php namespace mfe;
/**
 * MicroFramework Engine
 *
 * @author DeVinterX @ Dimitriy Kalugin <devinterx@gmail.com>
 * @link http://microframework.github.io/engine/
 * @copyright 2014 ZealoN Group, MicroFramework Group, Dimitriy Kalugin
 * @license http://microframework.github.io/license/
 * @package mfe
 * @version 1.0.3
 */
(version_compare(phpversion(), '5.5.0', '>=')) or die('MFE has needed PHP 5.5.0+');

//!if mod this, mod & doc before commit!
(defined('MFE_VERSION')) or define('MFE_VERSION', '1.0.3');
(defined('MFE_AUTOLOAD')) or define('MFE_AUTOLOAD', true);
(defined('MFE_TIME')) or define('MFE_TIME', microtime(true));

include_once __DIR__ . '/libs/autoload.php';

/**
 * Class mfe
 * @eng_desc This base class of logic MicroFramework, this class is Engine! This is MFE!
 * @rus_desc Это базовый класс реализующий двигатель MicroFramework. Это и есть MFE!
 *
 * @standards MFS-4.1, MFS-5
 * @package mfe
 */
final class mfe implements ImfeEngine, ImfeEventsManager, ImfeLoader {
    static public $DEBUG = false;

    /** @var mfe */
    static public $instance = null;
    static public $options = [];
    static public $register = [];

    use TmfeStandardEngineMethods;
    use TmfeStandardEventsMethods;
    use TmfeStandardLoaderMethods;
    use TmfeStandardComponentsMethods;
    use TmfeStandardApplicationMethods;

    private function __construct() {
        @ini_set('display_errors', false);

        set_error_handler(['mfe\CmfeRunHandler', 'errorHandler'], E_ALL);
        set_exception_handler(['mfe\CmfeRunHandler', 'exceptionHandler']);
        register_shutdown_function(['mfe\mfe', 'stopEngine']);
        $this->eventsMap['mfe.init'][] = function () {
            $this->startEngine();
        };
    }

    public function __destruct() {
        $time = round(microtime(true) - MFE_TIME, 3);
        file_put_contents('php://stdout', PHP_EOL . 'Done: ' . (($time >= 0.001) ? $time : '0.001') . ' ms');
    }

    // Here the engine is registered and prepares for work all components
    final public function startEngine() {
        global $_SERVER;
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
        if (self::loadMapFile('@libs.libs')) self::loadMap('libs');
        if (self::loadMapFile('@core.core')) self::loadMap('core');

        try {
            return self::trigger('engine.start');
        } catch (CmfeException $e) {
            CmfeDebug::criticalStopEngine(0x00000E1);
        }
        return false;
    }

    final static public function stopEngine() {
        if (!is_null(error_get_last()) && error_get_last()['type'] == 1)
            CmfeRunHandler::FatalErrorHandler();
        if (isset(self::$instance) && is_null(self::$instance)) return true;
        self::trigger('engine.stop');
        self::$instance = null;
        return true;
    }
}

/**
 * Auto register self in system
 * @standards MFS-5.5
 */
(mfe::option('MFE_AUTOLOAD')) ? (mfe::init()) : false;
