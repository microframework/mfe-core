<?php namespace mfe;
//TODO:: Разгрузить трейт,
/**
 * Class TmfeStandardApplicationMethods
 *
 * @property mixed event
 * @property mixed eventManager
 *
 * @property mixed loader
 *
 * @property mixed di
 * @property mixed componentManager
 * @package mfe
 */
trait TmfeStandardApplicationMethods {
    /** @var CmfeObjectsStack */
    protected $aliases = null;

    /** @var CmfeObjectsStack */
    protected $applications = null;

    /** @var CmfeObjectsStack */
    protected $components = null;

    /** @var CmfeObjectsStack */
    protected $events = null;

    /** @var array */
    protected $filesMap = null;

    /** @var array */
    static public $options = [
        'MFE_PHAR_INIT' => false,
        'MFE_AUTOLOAD' => false,

        'stackObject' => 'mfe\CmfeObjectsStack',
        'FileHelper' => 'mfe\CmfeSimpleFileHelper',
    ];

    /** @var TmfeStandardApplicationMethods */
    static private $instance = null;

    //TODO:: This is for DI
    /**
     * @param $key
     * @throws CmfeException
     */
    public function __get($key) {
        if (isset($this->components->components['di'])) {
            $di = &$this->di;
            return $di::getComponent($key);
        } elseif (isset($this->components->components['componentManager'])) {
            $componentManager = &$this->componentManager;
            return $componentManager::getComponent($key);
        }
        if (isset($this->$key)) {
            return (object)$this->$key;
        } elseif (isset($this->components->components[$key])) {
            return (object)$this->components->components[$key];
        }
        throw new CmfeException('Call unregistered component: ' . $key);
    }

    public function __isset($key) {
        if (isset($this->components->components['di'])) {
            $di = &$this->di;
            return $di::hasComponent($key);
        } elseif (isset($this->components->components['componentManager'])) {
            $componentManager = &$this->componentManager;
            return $componentManager::hasComponent($key);
        }
        if (isset($this->$key)) {
            return true;
        } else return false;
    }

    public function __unset($key) {
        return self::unRegisterComponent($key);
    }

    public function __call($method, $arguments) {
        if (isset($this->components->components['di'])) {
            $di = &$this->di;
            return $di::callComponent($method, $arguments);
        } elseif (isset($this->components->components['componentManager'])) {
            $componentManager = &$this->componentManager;
            return $componentManager::callComponent($method, $arguments);
        }
        //TODO:: Write default code here

        return null;
    }

    static public function __callStatic($method, $arguments) {
        if (is_null(self::$instance)) self::init();
        if (isset(self::$instance->components->components['di'])) {
            $di = &self::$instance->di;
            return $di::callCoreComponent($method, $arguments);
        } elseif (isset(self::$instance->components->components['componentManager'])) {
            $componentManager = &self::$instance->componentManager;
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
        throw new CmfeException('mfe can\'t be cloned');
    }

    final public function __sleep() {
        throw new CmfeException('mfe can\'t be serialized');
    }

    final public function __wakeup() {
        throw new CmfeException('mfe can\'t be serialized');
    }

    /** MFE Functions
     * @param callable $callback
     * @return object
     */
    /* +ImfeEngine */
    static public function init(\Closure $callback = null) {
        if (is_null(self::$instance)) {
            $class = get_called_class();
            self::$instance = new $class();
            if (is_callable($callback))
                $callback();
            self::trigger('mfe.init');
        }
        return (object)self::$instance;
    }

    //TODO:: Application stack
    static public function app($id = null) {
        if (is_null(self::$instance)) self::init();
        return $id;
    }

    static public function options($option) {
        if (defined($option) && constant($option) == true) {
            return self::$options[$option] = true;
        }
        return (isset(self::$options[$option])) ? self::$options[$option] : null;
    }

    /** Instance functions: /
     * /* +ImfeEventsManager */

    /**
     * @param $event_node
     * @return bool
     */
    public function registerEvent($event_node) {
        if (!is_string($event_node)) return false;
        self::trigger('event.register', [$event_node]);
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
        self::trigger('event.add', [$event_node, $callback]);
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
        self::trigger('event.remove', [$event_node, $callback]);
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
        if ($event_node !== 'event.fire') self::trigger('event.fire', [$event_node, $params]);
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
        self::trigger('event.clear', [$event_node]);
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
                                if ($node !== $part) $result[] = $node . $FileHelper::$SEPARATOR . $part;
                            }
                        } else $result[] = $part;
                    }
                } else {
                    $nodes = $result;
                    $result = [];
                    if (!empty($nodes)) {
                        foreach ($nodes as $node) {
                            $result[] = $node . $FileHelper::$SEPARATOR . $path;
                        }
                    } else $result[] = $path;
                }
            }
        } else $result[] = $path;
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
                    self::trigger('file.load', [$file . '.' . $extension . $EXT]);
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

    /* +ImfeLoader+Engine */
    static public function registerAlias($aliases, $dir) {
        if (is_null(self::$instance)) self::init();
        self::trigger('alias.register', [$aliases, $dir]);
        self::$instance->registerAliasDirectory($aliases, $dir);
    }

    static public function loadFile($file, $PHAR = false) {
        if (is_null(self::$instance)) self::init();
        if (isset(self::$instance->components->components['loader'])) {
            $loader = &self::$instance->loader;
            return $loader::loadFile($file, $PHAR);
        }
        return self::$instance->load($file, $PHAR);
    }

    static public function loadPhar($file) {
        self::trigger('phar.load', [$file]);
        if (isset(self::$instance->components->components['loader'])) {
            $loader = &self::$instance->loader;
            return $loader::loadPhar($file);
        }
        return self::loadFile($file, TRUE);
    }

    static public function loadCore($name) {
        self::trigger('file.loadCore', [$name]);
        if (isset(self::$instance->components->components['loader'])) {
            $loader = &self::$instance->loader;
            return $loader::loadCore($name);
        }
        return self::loadFile('@engine.@core.' . $name . '.core');
    }

    static public function loadMapFile($file) {
        self::trigger('file.loadMap', [$file]);
        if (isset(self::$instance->components->components['loader'])) {
            $loader = &self::$instance->loader;
            return $loader::loadMapFile($file);
        }
        return self::loadFile('@engine.' . $file . '.map');
    }

    static public function map($catalog, $index, $file) {
        if (isset(self::$instance->components->components['loader'])) {
            $loader = &self::$instance->loader;
            return $loader::map($file);
        }
        return self::$instance->filesMap[$catalog][$index] = $file;
    }

    static public function loadMap($map, $autoload = false) {
        self::trigger('map.load', [$map, $autoload]);
        if (isset(self::$instance->components->components['loader'])) {
            $loader = &self::$instance->loader;
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

    /* +ImfeComponentsManager+Engine */
    static public function registerComponent($name, $callback, $core = false, $override = false) {
        if (is_null(self::$instance)) self::init();
        self::trigger('component.register', [$name, $callback, $core, $override]);
        if (isset(self::$instance->components->components['di'])) {
            $di = &self::$instance->di;
            return $di::registerComponent($name, $callback, $core, $override);
        } elseif (isset(self::$instance->components->components['componentManager'])) {
            $componentManager = &self::$instance->componentManager;
            return $componentManager::registerComponent($name, $callback, $core, $override);
        }
        if (isset(self::$instance->components->coreComponents[$name]) && $override) {
            self::unRegisterComponent($name, $core);
        } elseif (isset(self::$instance->components->coreComponents[$name]) && !$override) {
            return false;
        }
        if (!$core) {
            if (is_array($callback) && count($callback) == 2) {
                if (class_exists($callback[0]) && method_exists($callback[0], $callback[1]))
                    return self::$instance->components->components[$name] = call_user_func_array($callback, []);
            } elseif (is_string($callback) || (is_array($callback) && count($callback) == 1)) {
                if (is_string($callback)) $callback = [$callback];
                if (is_subclass_of($callback[0], 'mfe\ImfeComponent')) {
                    return self::$instance->components->components[$name]
                        = call_user_func_array([$callback[0], 'registerComponent'], []);
                }
            }
        } else {
            if (is_array($callback) && count($callback) == 2) {
                if (class_exists($callback[0]) && method_exists($callback[0], $callback[1]))
                    return self::$instance->components->coreComponents[$name] = call_user_func_array($callback, []);
            } elseif (is_string($callback) || (is_array($callback) && count($callback) == 1)) {
                if (is_string($callback)) $callback = [$callback];
                if (is_subclass_of($callback[0], 'mfe\ImfeCoreComponent')) {
                    return self::$instance->components->coreComponents[$name]
                        = call_user_func_array([$callback[0], 'registerCoreComponent'], []);
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
            $di = &self::$instance->di;
            return $di::overrideComponent($name, $callback, $core);
        } elseif (isset(self::$instance->components->components['componentManager'])) {
            $componentManager = &self::$instance->componentManager;
            return $componentManager::overrideComponent($name, $callback, $core);
        }
        return self::registerComponent($name, $callback, $core, TRUE);
    }

    static public function overrideCoreComponent($name, $callback = null) {
        return self::overrideComponent($name, $callback, TRUE);
    }

    static public function unRegisterComponent($name, $core = false) {
        if (is_null(self::$instance)) self::init();
        self::trigger('component.unRegister', [$name, $core]);
        if (isset(self::$instance->components->components['di'])) {
            $di = &self::$instance->di;
            return $di::unRegisterComponent($name, $core);
        } elseif (isset(self::$instance->components->components['componentManager'])) {
            $componentManager = &self::$instance->componentManager;
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
