<?php namespace mfe;
/**
 * MicroFramework Engine
 *
 * @author DeVinterX @ Dimitriy Kalugin <devinterx@gmail.com>
 * @link http://microframework.github.io/engine/
 * @copyright 2014 ZealoN Group, MicroFramework Group, Dimitriy Kalugin
 * @license http://microframework.github.io/license/
 * @package mfe
 * @version 1.0.0
 */
(version_compare(phpversion(), '5.5.0', '>=')) or die('MFE has needed PHP 5.5.0+');

//!if mod this, mod & doc before commit!
(defined('MFE_VERSION')) or define('MFE_VERSION', '1.0.0');
(defined('MFE_AUTOLOAD')) or define('MFE_AUTOLOAD', true);

/**
 * Interface ImfeEngine
 * @eng_desc This interface dictates rules of writing of engines for MicroFramework
 * @rus_desc Этот интерфейс диктует правила написания двигателей для MicroFramework
 *
 * @standards MFS-4.1, MFS-5.[1,2]
 * @package mfe
 */
interface ImfeEngine {
    static function init();

    static function app($applicationName);

    static function trigger($eventName);

    static function on($eventName, $callback);

    static function off($eventName, $callback = null);

    static function registerComponent($name, $callback, $core = false, $override = false);

    static function overrideComponent($name, $callback = null, $core = false);

    static function unRegisterComponent($name, $core = false);
}


/**
 * Interface ImfeEvents
 * rr * @eng_desc This Interface dictates coding rules for events manager of MFE
 * @rus_desc Этот интерфейс диктует правила написания менеджера событий для MFE
 *
 * @standards MFS-5.6
 * @package mfe
 */
interface ImfeEventsManager {
    function registerEvent($event_node);

    function addEvent($event_node, $callback);

    function removeEvent($event_node, $callback);

    function fireEvent($event_node);

    function clearEvent($event_node);
}

/**
 * Interface ImfeLoader
 * @eng_desc This Interface dictates coding rules for loader of MFE
 * @rus_desc Этот интерфейс диктует правила написания Загружчика для MFE
 *
 * @standards MFS-1, MFS-2, MFS-4, MFS-5.3, MFS-6
 * @package mfe
 */
interface ImfeLoader {
    function load($file, $PHAR = false);

    function registerAliasDirectory($alias, $dir);

    static function loadFile($file, $PHAR = false);

    static function loadCore($file);

    static function loadPhar($file);

    static function loadMapFile($file);

    static function loadMap($mapName);

    static function map($catalog, $index, $file);
}

/**
 * Interface ImfeComponent
 * @package mfe
 */
interface ImfeComponent {
    function registerComponent();
}

/**
 * Interface ImfeCoreComponent
 * @package mfe
 */
interface ImfeCoreComponent {
    function registerCoreComponent();
}

/**
 * Interface ImfeObjectsStack
 * @package mfe
 */
interface ImfeObjectsStack {
    public function __set($key, $value);

    public function __get($key);

    public function __isset($key);

    public function add($key, $value);

    public function register($value);

    public function override($key, $value);

    public function remove($key);

    public function position($value);

    public function up($count_steps);

    public function down($count_steps);
}

//TODO:: ObjectStack with implemented interface ImfeObjectStack
class mfeObjectStack extends \ArrayObject {
}

class Exception extends \Exception {
}

/**
 * Class mfe
 * @eng_desc This base class of logic MicroFramework, this class is Engine! This is MFE!
 * @rus_desc Это базовый класс реализующий двигатель MicroFramework. Это и есть MFE!
 *
 * @property mixed event
 * @property mixed eventManager
 * @property mixed loader
 * @property mixed di
 * @property mixed componentManager
 *
 * @standards MFS-4.1, MFS-5
 * @package mfe
 */
final class mfe implements ImfeEngine, ImfeEventsManager, ImfeLoader {
    /** @var mfeObjectStack */
    protected $aliases = null;

    /** @var mfeObjectStack */
    protected $applications = null;

    /** @var mfeObjectStack */
    protected $components = null;

    /** @var mfeObjectStack */
    protected $events = null;

    /** @var array */
    protected $filesMap = null;

    /** @var array */
    static public $options = [
        'MFE_PHAR_INIT' => false,
        'MFE_AUTOLOAD' => false,

        'stackObject' => 'mfe\mfeObjectStack',
        'FileHelper' => 'mfe\mfeSimpleFileHelper',
    ];

    /** @var mfe */
    static private $instance = null;

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
    }

    public function __destruct() {
        $this->stopEngine();
    }

    //TODO:: This is for DI
    public function __get($key) {
        if (isset(self::$instance->components->components['di'])) {
            $di = & self::$instance->di;
            return $di::getComponent($key);
        } elseif (isset(self::$instance->components->components['componentManager'])) {
            $componentManager = & self::$instance->componentManager;
            return $componentManager::getComponent($key);
        }
        //TODO:: Write default code here
        return null;
    }

    public function __isset($key) {
        if (isset(self::$instance->components->components['di'])) {
            $di = & self::$instance->di;
            return $di::hasComponent($key);
        } elseif (isset(self::$instance->components->components['componentManager'])) {
            $componentManager = & self::$instance->componentManager;
            return $componentManager::hasComponent($key);
        }
        //TODO:: Write default code here
        return null;
    }

    public function __unset($key) {
        self::unRegisterComponent($key);
        //TODO:: Write default code here
        return null;
    }

    public function __call($method, $arguments) {
        if (isset(self::$instance->components->components['di'])) {
            $di = & self::$instance->di;
            return $di::callComponent($method, $arguments);
        } elseif (isset(self::$instance->components->components['componentManager'])) {
            $componentManager = & self::$instance->componentManager;
            return $componentManager::callComponent($method, $arguments);
        }
        //TODO:: Write default code here
        return null;
    }

    static public function __callStatic($method, $arguments) {
        if (is_null(self::$instance)) self::init();
        if (isset(self::$instance->components->components['di'])) {
            $di = & self::$instance->di;
            return $di::callCoreComponent($method, $arguments);
        } elseif (isset(self::$instance->components->components['componentManager'])) {
            $componentManager = & self::$instance->componentManager;
            return $componentManager::callCoreComponent($method, $arguments);
        }
        //TODO:: Write default code here
        return null;
    }

    //TODO:: This is for applicationManager()
    public function __invoke($arguments) {
    }

    // Close another magic functions
    final public function __set($key, $value) {
        return $this;
    }

    final public function __debugInfo() {
        return [MFE_VERSION];
    }

    final public function __toString() {
        return MFE_VERSION;
    }

    final static public function __set_state($array) {
        return [MFE_VERSION];
    }

    final public function __clone() {
        throw new Exception('mfe can\'t be cloned');
    }

    final public function __sleep() {
        throw new Exception('mfe can\'t be serialized');
    }

    final public function __wakeup() {
        throw new Exception('mfe can\'t be serialized');
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
        self::registerAlias('@core', 'libs');
        self::registerAlias('@core', 'core');

        //TODO:: Автоподстановка "*" по имени папки!
        self::loadMapFile('@core.*');

        self::trigger('startEngine');
    }

    final static public function stopEngine() {
        if (is_null(self::$instance)) return TRUE;
        self::trigger('stopEngine');
        return TRUE;
    }

    /** Instance functions: */
    /* +ImfeEventsManager */
    public function registerEvent($event_node) {
        if (!is_string($event_node)) return false;
        self::trigger('registerEvent', [$event_node]);
        if (isset($this->components->components['event'])) {
            return $this->event->registerEvent($event_node);
        } elseif (isset($this->components->components['eventManager'])) {
            return $this->eventManager->registerEvent($event_node);
        } else {
            if (!isset($this->events[$event_node]))
                $this->events[$event_node] = new \ArrayObject();
        }
        return true;
    }

    public function addEvent($event_node, $callback) {
        if (!is_string($event_node)) return false;
        self::trigger('addEvent', [$event_node, $callback]);
        if (!isset($this->events[$event_node])) $this->registerEvent($event_node);
        if (isset($this->components->components['event'])) {
            return $this->event->addEvent($event_node, $callback);
        } elseif (isset($this->components->components['eventManager'])) {
            return $this->eventManager->addEvent($event_node, $callback);
        } else {
            $this->events[$event_node][] = $callback;
        }
        return true;
    }

    public function removeEvent($event_node, $callback) {
        if (!is_string($event_node)) return false;
        self::trigger('removeEvent', [$event_node, $callback]);
        if (!isset($this->events[$event_node])) return true;
        if (isset($this->components->components['event'])) {
            return $this->event->removeEvent($event_node, $callback);
        } elseif (isset($this->components->components['eventManager'])) {
            return $this->eventManager->removeEvent($event_node, $callback);
        } else {
            $key = array_search($callback, $this->events[$event_node]);
            if ($key || $key === 0) unset($this->events[$event_node][$key]);
            return true;
        }
    }

    public function fireEvent($event_node, $params = []) {
        if (!is_string($event_node)) return false;
        if ($event_node !== 'fireEvent') self::trigger('fireEvent', [$event_node, $params]);
        if (isset($this->components->components['event'])) {
            return $this->event->fireEvent($event_node);
        } elseif (isset($this->components->components['eventManager'])) {
            return $this->eventManager->fireEvent($event_node);
        } else {
            if (!isset($this->events[$event_node])) return false;
            foreach ($this->events[$event_node] as $event) {
                if (is_object($event) && is_callable($event)) {
                    // TODO:: Fix second param, to link with stats object
                    $event($params, static::$instance);
                } elseif (is_string($event)
                    && isset($this->events[$event])
                    && $event_node !== $event
                ) {
                    self::trigger($event);
                }
            }
        }
        return true;
    }

    public function clearEvent($event_node) {
        if (!is_string($event_node)) return false;
        self::trigger('clearEvent', [$event_node]);
        if (isset($this->components->components['event'])) {
            return $this->event->clearEvent($event_node);
        } elseif (isset($this->components->components['eventManager'])) {
            return $this->eventManager->clearEvent($event_node);
        } else {
            if (!isset($this->events[$event_node])) return true;
            $this->events[$event_node] = [];
        }
        return false;
    }
    /* -ImfeEventsManager */

    /* +ImfeLoader */
    public function registerAliasDirectory($aliases, $dir) {
        if (isset($this->components->components['loader'])) {
            return $this->loader->registerAliasDirectory($aliases, $dir);
        }
        if (is_array($aliases)) {
            foreach ($aliases as $alias) {
                if ((string)$alias[0] === '@') {
                    $this->aliases[strtolower($alias)][] = str_replace(['\\', '//'], '/', $dir);
                }
            }
        } else {
            if ((string)$aliases[0] === '@') {
                $this->aliases[strtolower($aliases)][] = str_replace(['\\', '//'], '/', $dir);
            }
        }
        return $this;
    }

    protected function getRealPaths($path) {
        $FileHelper = self::$options['FileHelper'];
        $result = [];
        $path_nodes = explode('.', $path);
        if (!empty($path_nodes) && count($path_nodes) >= 2) {
            $extension = array_reverse($path_nodes)[0];
            unset($path_nodes[count($path_nodes) - 1]);
            foreach ($path_nodes as $path) {
                if (isset($this->aliases[$path])) {
                    $nodes = $result;
                    $result = [];
                    foreach (array_reverse($this->aliases[$path]) as $part) {
                        if (!empty($nodes)) {
                            foreach ($nodes as $node) {
                                if ($node !== $part) {
                                    $result[] = $node . $FileHelper::$SEPARATOR . $part;
                                }
                            }
                        } else {
                            $result[] = $part;
                        }
                    }
                } else {
                    $nodes = $result;
                    $result = [];
                    if (!empty($nodes)) {
                        foreach ($nodes as $node) {
                            $result[] = $node . $FileHelper::$SEPARATOR . $path;
                        }
                    } else {
                        $result[] = $path;
                    }
                }
            }
        } else {
            $result[] = $path;
        }
        if (isset($extension)) $result['extension'] = $extension;
        return $result;
    }

    public function load($file, $PHAR = false) {
        if (isset($this->components->components['loader'])) {
            return $this->loader->load($file, $PHAR);
        } else {
            $FileHelper = self::$options['FileHelper'];
            $EXT = (!$PHAR) ? $FileHelper::$PHP : $FileHelper::$Phar;
            $paths = $this->getRealPaths($file);
            if (isset($paths['extension'])) {
                $extension = $paths['extension'];
                unset($paths['extension']);
            } else $extension = '';
            foreach ($paths as $file) {
                print $file . '.' . $extension . $EXT . PHP_EOL;
                if (file_exists($file . '.' . $extension . $EXT)) {
                    self::trigger('loadFile', [$file . '.' . $extension . $EXT]);
                    /** @noinspection PhpIncludeInspection */
                    return include_once $file . '.' . $extension . $EXT;
                }
            }
            return false;
        }
    }

    public function loadFilesFromMap($name) {
        if (isset($this->filesMap[$name])
            && is_array($this->filesMap[$name])
            && !empty($this->filesMap[$name])
        ) {
            foreach ($this->filesMap[$name] as $file) {
                $this->load($file);
            }
        }
        return $this;
    }
    /* -ImfeLoader */

    /** MFE Functions */
    /* +ImfeEngine */
    static final public function init() {
        if (is_null(self::$instance)) {
            $class = get_called_class();
            self::$instance = new $class();
            self::on('mfeInit', function () {
                self::$instance->startEngine();
            });
            self::trigger('mfeInit');
        }
        return (object)self::$instance;
    }

    static public function options($option) {
        if ($option == 'MFE_AUTOLOAD'
            && defined('MFE_AUTOLOAD')
            && MFE_AUTOLOAD == true
        ) {
            return self::$options['MFE_AUTOLOAD'] = true;
        }
        if ($option == 'MFE_PHAR_INIT'
            && defined('MFE_PHAR_INIT')
            && MFE_PHAR_INIT == true
        ) {
            return self::$options['MFE_PHAR_INIT'] = true;
        }
        return (isset(self::$options[$option])) ? self::$options[$option] : null;
    }

    static public function app($id = null) {
        if (is_null(self::$instance)) self::init();
    }

    /* ++ImfeEventsManager+Engine */
    static public function trigger($event, $params = []) {
        if (is_null(self::$instance)) self::init();
        self::$instance->fireEvent($event, $params);
    }

    static public function on($event, $callback) {
        if (is_null(self::$instance)) self::init();
        self::$instance->addEvent($event, $callback);
    }

    static public function off($event, $callback = null) {
        if (is_null(self::$instance)) self::init();
        if (is_null($callback)) {
            self::$instance->clearEvent($event);
        } else self::$instance->removeEvent($event, $callback);
    }
    /* --ImfeEventsManager+Engine */
    /* -ImfeEngine */

    /* +ImfeLoader+Engine */
    static public function registerAlias($aliases, $dir) {
        if (is_null(self::$instance)) self::init();
        self::trigger('registerAlias', [$aliases, $dir]);
        self::$instance->registerAliasDirectory($aliases, $dir);
    }

    static public function loadFile($file, $PHAR = false) {
        if (is_null(self::$instance)) self::init();
        if (isset(self::$instance->components->components['loader'])) {
            $loader = & self::$instance->loader;
            return $loader::loadFile($file, $PHAR);
        }
        return self::$instance->load($file, $PHAR);
    }

    static public function loadPhar($file) {
        self::trigger('loadPhar', [$file]);
        if (isset(self::$instance->components->components['loader'])) {
            $loader = & self::$instance->loader;
            return $loader::loadPhar($file);
        }
        return self::loadFile($file, TRUE);
    }

    static public function loadCore($name) {
        self::trigger('loadCoreFile', [$name]);
        if (isset(self::$instance->components->components['loader'])) {
            $loader = & self::$instance->loader;
            return $loader::loadCore($name);
        }
        return self::loadFile('@engine.@core.' . $name . '.'.$name);
    }

    static public function loadMapFile($file) {
        self::trigger('loadMapFile', [$file]);
        if (isset(self::$instance->components->components['loader'])) {
            $loader = & self::$instance->loader;
            return $loader::loadMapFile($file);
        }
        return self::loadFile('@engine.' . $file . '.map');
    }

    static public function map($catalog, $index, $file) {
        if (isset(self::$instance->components->components['loader'])) {
            $loader = & self::$instance->loader;
            return $loader::map($file);
        }
        return self::$instance->filesMap[$catalog][$index] = $file;
    }

    static public function loadMap($map, $autoload = false) {
        self::trigger('loadMap', [$map, $autoload]);
        if (isset(self::$instance->components->components['loader'])) {
            $loader = & self::$instance->loader;
            return $loader::loadMap($map);
        }
        if (is_string($map)) {
            return self::$instance->loadFilesFromMap($map);
        } elseif (is_array($map)) {
            foreach ($map as $list) {
                if (count($list) >= 3)
                    self::map($list[0], $list[1], $list[2]);
            }
            if ($autoload && is_string($autoload)) {
                self::loadMap($autoload);
            }
            if ($autoload && is_array($autoload)) {
                foreach ($autoload as $list) {
                    if (is_string($list)) self::loadMap($list);
                }
            }
        }
        return true;
    }
    /* -ImfeLoader+Engine */

    /* +ImfeComponentsManager+Engine */
    static public function registerComponent($name, $callback, $core = false, $override = false) {
        if (is_null(self::$instance)) self::init();
        self::trigger('registerComponent', [$name, $callback, $core, $override]);
        if (isset(self::$instance->components->components['di'])) {
            $di = & self::$instance->di;
            return $di::registerComponent($name, $callback, $core, $override);
        } elseif (isset(self::$instance->components->components['componentManager'])) {
            $componentManager = & self::$instance->componentManager;
            return $componentManager::registerComponent($name, $callback, $core, $override);
        }
        if (isset(self::$instance->components->coreComponents[$name]) && !$override) {
            return false;
        } else self::unRegisterComponent($name, $core);
        if (!$core) {
            if (is_array($callback) && count($callback) == 2) {
                if (class_exists($callback[0])
                    && method_exists($callback[0], $callback[1])
                ) {
                    self::$instance->components->coreComponents[$name] = $callback;
                    if ($callback[0] instanceof ImfeComponent)
                        return call_user_func_array([$callback[0], 'registerComponent'], []);
                    return true;
                }
            }
        } else {
            if (is_array($callback) && count($callback) == 2) {
                if (class_exists($callback[0])
                    && method_exists($callback[0], $callback[1])
                ) {
                    self::$instance->components->coreComponents[$name] = $callback;
                    if ($callback[0] instanceof ImfeCoreComponent)
                        return call_user_func_array([$callback[0], 'registerCoreComponent'], []);
                    return true;
                }
            }
        }
        return false;
    }

    static public function registerCoreComponent($name, $callback) {
        return self::registerComponent($name, $callback, TRUE);
    }

    static public function overrideComponent($name, $callback = null, $core = false) {
        if (is_null(self::$instance)) self::init();
        if (isset(self::$instance->components->components['di'])) {
            $di = & self::$instance->di;
            return $di::overrideComponent($name, $callback, $core);
        } elseif (isset(self::$instance->components->components['componentManager'])) {
            $componentManager = & self::$instance->componentManager;
            return $componentManager::overrideComponent($name, $callback, $core);
        }
        return self::registerComponent($name, $callback, $core, TRUE);
    }

    static public function overrideCoreComponent($name, $callback = null) {
        return self::overrideComponent($name, $callback, TRUE);
    }

    static public function unRegisterComponent($name, $core = false) {
        if (is_null(self::$instance)) self::init();
        self::trigger('unRegisterComponent', [$name, $core]);
        if (isset(self::$instance->components->components['di'])) {
            $di = & self::$instance->di;
            return $di::unRegisterComponent($name, $core);
        } elseif (isset(self::$instance->components->components['componentManager'])) {
            $componentManager = & self::$instance->componentManager;
            return $componentManager::unRegisterComponent($name, $core);
        }

        if (self::$instance->components->coreComponents[$name] && !$core) {
            unset(self::$instance->components->coreComponents[$name]);
        } elseif (self::$instance->components->components[$name] && $core) {
            unset(self::$instance->components->components[$name]);
        }
        return true;
    }
    /* -ImfeComponentsManager+Engine */
}

/**
 * Simple FileHelper
 */
if (!class_exists('mfe\mfeSimpleFileHelper')
    && mfe::$options['FileHelper'] === 'mfe\mfeSimpleFileHelper'
) {
    class mfeSimpleFileHelper {
        static public $SEPARATOR = '/';
        static public $PHP = '.php';
        static public $Phar = '.phar';

        final static public function scandir_recursive($dir, $trim = null) {
            $result = [];
            if (is_null($trim)) $trim = strlen($dir);
            if (file_exists($dir) && is_dir($dir)) {
                $path = scandir($dir);
                foreach ($path as $fileInfo) {
                    $file = $dir . self::$SEPARATOR . $fileInfo;
                    if (is_dir($file) && $fileInfo != '.' && $fileInfo != '..') {
                        $result = array_merge($result, self::scandir_recursive($file, $trim));
                    }
                    if (is_file($file) && $fileInfo != '.' && $fileInfo != '..') {
                        $line = substr($file, $trim);
                        if ('/' == substr($line, 0, 1) || '\\' == substr($line, 0, 1)) $line = substr($line, 1);
                        $result[] = str_replace(['//'], '/', $line);
                    }
                }
            }
            return $result;
        }
    }
}

/**
 * Auto register self in system
 * @standards MFS-5.5
 */
(mfe::options('MFE_AUTOLOAD')) ? (mfe::init()) : false;
