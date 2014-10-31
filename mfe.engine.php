<?php namespace mfe;
/**
 * MicroFramework Engine
 *
 * @author DeVinterX @ Dimitriy Kalugin <devinterx@gmail.com>
 * @link http://microframework.github.io/engine/
 * @copyright 2014 ZealoN Group, MicroFramework Group, Dimitriy Kalugin
 * @license http://microframework.github.io/license/
 * @package mfe
 * @version 1.0.1
 */
(version_compare(phpversion(), '5.5.0', '>=')) or die('MFE has needed PHP 5.5.0+');

//!if mod this, mod & doc before commit!
(defined('MFE_VERSION')) or define('MFE_VERSION', '1.0.1');
(defined('MFE_AUTOLOAD')) or define('MFE_AUTOLOAD', true);

include_once __DIR__.'/libs/autoload.php';

/**
 * Class mfe
 * @eng_desc This base class of logic MicroFramework, this class is Engine! This is MFE!
 * @rus_desc Это базовый класс реализующий двигатель MicroFramework. Это и есть MFE!
 *
 * @standards MFS-4.1, MFS-5
 * @package mfe
 */
final class mfe implements ImfeEngine, ImfeEventsManager, ImfeLoader {
    use TmfeStandardApplicationMethods;

    private function __construct() {
        $stack = self::$options['stackObject'];

        $this->aliases = new $stack();
        $this->applications = new $stack();
        $this->components = new $stack([
            'coreComponents' => new $stack(),
            'components' => new $stack()
        ]);
        $this->events = new $stack();
        $this->filesMap = new $stack();

        register_shutdown_function(['mfe\mfe', 'stopEngine']);
        $this->events['mfeInit'][] = function () {
            $this->startEngine();
        };
    }

    public function __destruct() {
        $this->stopEngine();
    }

    //TODO:: Тут регистрируется движок и готовится к работе все компоненты!
    final public function startEngine() {
        global $_SERVER;
        //TODO:: Where from phar archive register specific paths
        if (self::options('MFE_PHAR_INIT')) {

        }

        $RUN = array_reverse(explode('/', $_SERVER['SCRIPT_NAME']))[0];
        $REAL_PATH = dirname(realpath($RUN));

        self::registerAlias('@engine', __DIR__);
        if (__DIR__ != $REAL_PATH)
            self::registerAlias('ENGINE', $REAL_PATH . '/engine');
        self::registerAlias('@libs', 'libs');
        self::registerAlias('@core', 'core');

        //Load main libs & core files by map file!
        self::loadMapFile('@libs.libs');
        self::loadMapFile('@core.core');

        self::trigger('startEngine');
    }

    final static public function stopEngine() {
        if (is_null(self::$instance)) return TRUE;
        self::trigger('stopEngine');
        return TRUE;
    }
}

/**
 * Auto register self in system
 * @standards MFS-5.5
 */
(mfe::options('MFE_AUTOLOAD')) ? (mfe::init()) : false;
