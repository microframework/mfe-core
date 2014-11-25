<?php namespace mfe;
/**
 * MicroFramework Engine
 *
 * @author DeVinterX @ Dimitriy Kalugin <devinterx@gmail.com>
 * @link http://microframework.github.io/engine/
 * @copyright 2014 ZealoN Group, MicroFramework Group, Dimitriy Kalugin
 * @license http://microframework.github.io/license/
 * @package mfe
 * @version 1.0.2
 */
(version_compare(phpversion(), '5.5.0', '>=')) or die('MFE has needed PHP 5.5.0+');

//!if mod this, mod & doc before commit!
(defined('MFE_VERSION')) or define('MFE_VERSION', '1.0.2');
(defined('MFE_AUTOLOAD')) or define('MFE_AUTOLOAD', true);

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

    use TmfeStandardEngineMethods;
    use TmfeStandardEventsMethods;
    use TmfeStandardLoaderMethods;
    use TmfeStandardComponentsMethods;
    use TmfeStandardApplicationMethods;

    private function __construct() {
        $stack = self::option('stackObject');

        $this->aliases = new $stack();
        $this->applications = new $stack();
        $this->components = new $stack([
            'coreComponents' => new $stack(),
            'components' => new $stack()
        ]);
        $this->eventsMap = new $stack();
        $this->filesMap = new $stack();

        register_shutdown_function(['mfe\mfe', 'stopEngine']);
        $this->eventsMap['mfe.init'][] = function () {
            $this->startEngine();
        };
    }

    public function __destruct() {
        $this->stopEngine();
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
        if (__DIR__ !== $REAL_PATH && file_exists($REAL_PATH . '/engine') && is_dir($REAL_PATH . '/engine'))
            self::registerAlias('@engine', $REAL_PATH . '/engine');
        self::registerAlias('@libs', 'libs');
        self::registerAlias('@core', 'core');

        //Load main libs & core files by map file!
        if (self::loadMapFile('@libs.libs')) self::loadMap('libs');
        if (self::loadMapFile('@core.core')) self::loadMap('core');

        try {
            return self::trigger('engine.start');
        } catch (CmfeException $e) {CmfeDebug::criticalStopEngine(0x00000E1);}
        return false;
    }

    final static public function stopEngine() {
        if (is_null(self::$instance)) return true;
        self::trigger('engine.stop');
        return true;
    }
}

/**
 * Auto register self in system
 * @standards MFS-5.5
 */
(mfe::option('MFE_AUTOLOAD')) ? (mfe::init()) : false;
