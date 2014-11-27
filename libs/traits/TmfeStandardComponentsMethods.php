<?php namespace mfe;

/**
 * Class TmfeStandardComponentsMethods
 *
 * @static mfe init()
 * @property mixed di
 * @property mixed componentManager
 *
 * @package mfe
 */
trait TmfeStandardComponentsMethods {
    /** @var CmfeObjectsStack */
    protected $components = null;

    static public function TmfeStandardComponentsMethodsInit() {
        mfe::$register[] = 'components';
    }

    protected function __TmfeStandardComponentsMethodsInit() {
        $stackObject = mfe::option('stackObject');
        /** @var CmfeObjectsStack $components */
        $components = new $stackObject([
            'coreComponents' => new $stackObject([], 0),
            'components' => new $stackObject([], 1)
        ]);
        $this->components = $components;
    }

    final public function __set($key, $value) {
        return $this->components->$key = $value;
    }

    public function __get($key) {
        $class = get_called_class();
        /** @var mfe $class */
        if (isset($this->components->components['di'])) {
            return call_user_func_array([get_class($class::init()->di), 'getComponent'], [$key]);
        } elseif (isset($this->components->components['componentManager'])) {
            return call_user_func_array([get_class($class::init()->componentManager), 'getComponent'], [$key]);
        }
        if (isset($this->$key) && !isset($this->components->components[$key])) {
            return (object)$this->$key;
        } elseif (!isset($this->$key) && isset($this->components->components[$key])) {
            return (object)$this->components->components[$key];
        }
        throw new CmfeException('Call unregistered component: ' . $key);
    }

    public function __unset($key) {
        return self::unRegisterComponent($key);
    }

    public function __call($method, $arguments) {
        $class = get_called_class();
        /** @var mfe $class */
        if (isset($this->components->components['di'])) {
            return call_user_func_array([get_class($class::init()->di), 'callComponent'], [$method, $arguments]);
        } elseif (isset($this->components->components['componentManager'])) {
            return call_user_func_array([get_class($class::init()->componentManager), 'callComponent'], [$method, $arguments]);
        }
        if (isset($this->components->components[$method])) {
            return call_user_func_array([get_class($this->components->components[$method]), '__invoke'], $arguments);
        }
        throw new CmfeException("Call undefined method: {$method}");
    }

    static public function __callStatic($method, $arguments) {
        $class = get_called_class();
        /** @var mfe $class */
        if (isset($class::init()->di)) {
            return call_user_func_array([get_class($class::init()->di), 'callCoreComponent'], [$method, $arguments]);
        } elseif (isset($class::init()->componentManager)) {
            return call_user_func_array([get_class($class::init()->componentManager), 'callCoreComponent'], [$method, $arguments]);
        }
        if ($class::init()->hasCoreComponent($method)) {
            return call_user_func_array([get_class($class::init()->getCoreComponent($method)), '__invoke'], $arguments);
        }
        throw new CmfeException("Call undefined core method: {$method}");
    }

    public function hasComponent($method) {
        if (isset($this->components->components[$method])) {
            return true;
        }
        return false;
    }

    public function getComponent($method) {
        if (isset($this->components->components[$method])) {
            return $this->components->components[$method];
        }
        return false;
    }

    public function hasCoreComponent($method) {
        if (isset($this->components->coreComponents[$method])) {
            return true;
        }
        return false;
    }

    public function getCoreComponent($method) {
        if (isset($this->components->coreComponents[$method])) {
            return $this->components->coreComponents[$method];
        }
        return false;
    }

    static public function registerComponent($name, $callback, $core = false, $override = false) {
        $class = get_called_class();
        /** @var mfe $class */
        $class::trigger('component.register', [$name, $callback, $core, $override]);
        if (isset($class::init()->di)) {
            return call_user_func_array([get_class($class::init()->di), __METHOD__], [$name, $callback, $core, $override]);
        } elseif (isset($class::init()->componentManager)) {
            return call_user_func_array([get_class($class::init()->componentManager), __METHOD__], [$name, $callback, $core, $override]);
        }
        if (isset($class::init()->components->coreComponents[$name]) && $override) {
            self::unRegisterComponent($name, $core);
        } elseif (isset($class::init()->components->coreComponents[$name]) && !$override) {
            return false;
        }
        if (!$core) {
            if (is_array($callback) && count($callback) == 2) {
                if (class_exists($callback[0]) && method_exists($callback[0], $callback[1]))
                    return mfe::init()->components->components[$name] = call_user_func_array($callback, []);
            } elseif (is_string($callback) || (is_array($callback) && count($callback) == 1)) {
                if (is_string($callback)) $callback = [$callback];
                if (is_subclass_of($callback[0], 'mfe\ImfeComponent')) {
                    return mfe::init()->components->components[$name]
                        = call_user_func_array([$callback[0], 'registerComponent'], []);
                }
            }
        } else {
            if (is_array($callback) && count($callback) == 2) {
                if (class_exists($callback[0]) && method_exists($callback[0], $callback[1]))
                    return mfe::init()->components->coreComponents[$name] = call_user_func_array($callback, []);
            } elseif (is_string($callback) || (is_array($callback) && count($callback) == 1)) {
                if (is_string($callback)) $callback = [$callback];
                if (is_subclass_of($callback[0], 'mfe\ImfeCoreComponent')) {
                    return mfe::init()->components->coreComponents[$name]
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
        $class = get_called_class();
        /** @var mfe $class */
        if (isset($class::init()->di)) {
            return call_user_func_array([get_class($class::init()->di), __METHOD__], [$name, $callback, $core]);

        } elseif (isset($class::init()->componentManager)) {
            return call_user_func_array([get_class($class::init()->componentManager), __METHOD__], [$name, $callback, $core]);
        }
        return self::registerComponent($name, $callback, $core, TRUE);
    }

    static public function overrideCoreComponent($name, $callback = null) {
        return self::overrideComponent($name, $callback, TRUE);
    }

    static public function unRegisterComponent($name, $core = false) {
        $class = get_called_class();
        /** @var mfe $class */
        $class::trigger('component.unRegister', [$name, $core]);
        if (isset($class::init()->di)) {
            return call_user_func_array([get_class($class::init()->di), __METHOD__], [$name, $core]);
        } elseif (isset($class::init()->componentManager)) {
            return call_user_func_array([get_class($class::init()->componentManager), __METHOD__], [$name, $core]);
        }

        if ($class::init()->components->coreComponents[$name] && !$core) {
            unset($class::init()->components->coreComponents[$name]);
        } elseif ($class::init()->components->components[$name] && $core) {
            unset($class::init()->components->components[$name]);
        }
        return true;
    }
}
