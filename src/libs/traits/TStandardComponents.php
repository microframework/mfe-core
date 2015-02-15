<?php namespace mfe\core\libs\traits;

use mfe\core\libs\components\CException;
use mfe\core\libs\components\CObjectsStack;
use mfe\core\mfe;
use Symfony\Component\Config\Definition\Exception\Exception;

//TODO:: Полная документация

/**
 * Trait TStandardComponents
 *
 * @property mixed di
 * @property mixed componentManager
 *
 * @method mixed di
 * @method mixed componentManager
 *
 * @method static bool hasComponent
 * @method static callable|null getComponent
 * @method static bool hasCoreComponent
 * @method static callable|null getCoreComponent
 * @method static bool hasClosingComponent
 * @method static callable|null getClosingComponent
 * @method static bool|callable registerComponent
 * @method static bool|callable registerCoreComponent
 * @method static bool|callable registerClosingComponent
 * @method static null unRegisterComponent
 * @method static null unRegisterCoreComponent
 * @method static null unRegisterClosingComponent
 *
 * @package mfe\core\libs\traits
 */
trait TStandardComponents
{
    /** @var CObjectsStack */
    public $components;

    /** @var CObjectsStack */
    protected $closingComponents;

    /**
     * Behavior trait constructor
     */
    static protected function TStandardComponents()
    {
        mfe::$register[] = 'components';
    }

    /**
     * Trait constructor
     */
    protected function __TStandardComponents()
    {
        /** @var mfe $class */
        $class = get_called_class();

        $stackObject = mfe::option('stackObject');
        /** @var CObjectsStack $components */
        $this->components = new $stackObject([
            'coreComponents' => new $stackObject([], 0),
            'components' => new $stackObject([], 1)
        ]);
        $this->closingComponents = new $stackObject;

        $this->registerStandardComponents();

        $this->_registerClosingComponent('di', $class);
        $this->_registerClosingComponent('componentManager', $class);
    }

    protected function registerStandardComponents($undo = false)
    {
        /** @var mfe $class */
        $class = get_called_class();

        $components = [
            'hasComponent' => [$class, '_hasComponent'],
            'getComponent' => [$class, '_getComponent'],
            'callComponent' => [$class, '_callComponent'],
            'hasCoreComponent' => [$class, '_hasCoreComponent'],
            'getCoreComponent' => [$class, '_getCoreComponent'],
            'callCoreComponent' => [$class, '_callCoreComponent'],
            'hasClosingComponent' => [$class, '_hasClosingComponent'],
            'getClosingComponent' => [$class, '_getClosingComponent'],
            'callClosingComponent' => [$class, '_callClosingComponent'],
            'registerComponent' => [$class, '_registerComponent'],
            'registerCoreComponent' => [$class, '_registerCoreComponent'],
            'registerClosingComponent' => [$class, '_registerClosingComponent'],
            'unRegisterComponent' => [$class, '_unRegisterComponent'],
            'unRegisterCoreComponent' => [$class, '_unRegisterCoreComponent'],
            'unRegisterClosingComponent' => [$class, '_unRegisterClosingComponent']
        ];

        foreach ($components as $key => $callback) {
            (!$undo) ? $this->_registerCoreComponent($key, $callback)
                : $this->_unRegisterCoreComponent($key);
        }

        return $components;
    }

    /**
     * Set component
     *
     * @param $key , Name of system component or system public property
     * @param $value , Value or callback of system component
     * @return callable|mixed
     */
    public function __set($key, $value)
    {
        /** @var mfe $class */
        $class = get_called_class();

        if ($this->_hasComponent('di')) {
            return call_user_func_array([get_class($class::getInstance()->di), '_setComponent'], [$key, $value]);
        } elseif ($this->_hasComponent('componentManager')) {
            return call_user_func_array([get_class($class::getInstance()->componentManager), '_setComponent'], [$key, $value]);
        }

        $setter = 'set' . ucfirst($key);
        if (method_exists($this, $setter) && (new \ReflectionObject($this))->getMethod($setter)->isPublic()) {
            return $this->$setter($value);
        }

        return $this->components->$key = $value;
    }

    /**
     * Get component
     *
     * @param $key , Name of system component or system public property
     * @return callable|mixed|null
     * @throws CException, 'Try to get not public property', 'Try to get unregistered component'
     */
    public function __get($key)
    {
        /** @var mfe $class */
        $class = get_called_class();

        if ($this->_hasComponent('di')) {
            return call_user_func_array([get_class($class::getInstance()->di), '_getComponent'], [$key]);
        } elseif ($this->_hasComponent('componentManager')) {
            return call_user_func_array([get_class($class::getInstance()->componentManager), '_getComponent'], [$key]);
        }

        if (isset($this->$key) && !$this->_hasComponent($key)) {
            if (!(new \ReflectionObject($this))->getProperty($key)->isPublic()) {
                $getter = 'get' . ucfirst($key);
                if (method_exists($this, $getter) && (new \ReflectionObject($this))->getMethod($getter)->isPublic()) {
                    return $this->$getter();
                }
            } else {
                throw new CException('Try to get not public property: ' . $key);
            }
        } elseif ($this->_hasComponent($key)) {
            if (is_callable($this->components->components[$key])) {
                return call_user_func_array($this->components->components[$key], []);
            }
            return $this->_getComponent($key);
        }

        if ($this->_hasClosingComponent($key)) {
            if (is_callable([$this->closingComponents[$key], 'getInstance'])) {
                return call_user_func_array([$this->closingComponents[$key], 'getInstance'], []);
            }
            return $this->_getClosingComponent($key);
        }

        throw new CException('Try to get unregistered component: ' . $key);
    }

    /**
     * Delete component
     *
     * @param $key , Name of system component or system public property
     * @throws CException, 'Unset not public property'
     * @return bool|null
     */
    public function __unset($key)
    {
        /** @var mfe $class */
        $class = get_called_class();

        if ($this->_hasComponent('di')) {
            return call_user_func_array([get_class($class::getInstance()->di), '_unsetComponent'], [$key]);
        } elseif ($this->_hasComponent('componentManager')) {
            return call_user_func_array([get_class($class::getInstance()->componentManager), '_unsetComponent'], [$key]);
        }

        if (isset($this->$key) && !$this->_hasComponent($key)) {
            if (!(new \ReflectionObject($this))->getProperty($key)->isPublic()) {
                $unSetter = 'unset' . ucfirst($key);
                if (method_exists($this, $unSetter) && (new \ReflectionObject($this))->getMethod($unSetter)->isPublic()) {
                    return $this->$unSetter();
                }
            } else {
                throw new CException('Unset not public property: ' . $key);
            }
        } elseif ($this->_hasComponent($key)) {
            return $this->components->components[$key] = null;
        }
        return false;
    }

    /**
     * Call component
     *
     * Calls a dependent method of system or tries to transfer control of his call in DI
     * Вызывает зависимый метод системы или пытается передать управление его вызовом в DI
     *
     * @param $method , Name method of system or component
     * @param array $arguments
     * @return callable|mixed, Result of execution of a component
     * @throws CException, 'Call undefined method'
     */
    public function __call($method, $arguments = [])
    {
        /** @var mfe $class */
        $class = get_called_class();

        if ($this->_hasComponent('di')) {
            return call_user_func_array([get_class($class::getInstance()->di), '_callComponent'], [$method, $arguments]);
        } elseif ($this->_hasComponent('componentManager')) {
            return call_user_func_array([get_class($class::getInstance()->componentManager), '_callComponent'], [$method, $arguments]);
        }

        if ($this->_hasComponent($method)) {
            return $this->_callComponent($method, $arguments);
        }

        if ($this->_hasCoreComponent($method)) {
            return $this->_callCoreComponent($method, $arguments);
        }

        if ($this->_hasClosingComponent($method)) {
            if (is_callable([$this->closingComponents[$method], 'getInstance'])) {
                return call_user_func_array([$this->closingComponents[$method], 'getInstance'], $arguments);
            }
            return $this->_getClosingComponent($method);
        }

        throw new CException("Call undefined method: {$method}");
    }

    /**
     * Call core component
     *
     * Calls a dependent method of a core or tries to transfer control of his call in DI
     * Вызывает зависимый метод ядра или пытается передать управление его вызовом в DI
     *
     * @param $method , Name method of system or component
     * @param array $arguments
     * @return callable|mixed, Result of execution of a component
     * @throws CException, 'Call undefined core method'
     */
    static public function __callStatic($method, $arguments = [])
    {
        /** @var mfe $class */
        $class = get_called_class();

        if ($class::getInstance()->_hasComponent('di')) {
            return $class::getInstance()->di->_callCoreComponent($method, $arguments);
        } elseif ($class::getInstance()->_hasComponent('componentManager')) {
            return $class::getInstance()->componentManager->_callCoreComponent($method, $arguments);
        }

        if ($class::getInstance()->_hasCoreComponent($method)) {
            return $class::getInstance()->_callCoreComponent($method, $arguments);
        }
        throw new CException("Call undefined core method: {$method}");
    }

    /**
     * Check component
     *
     * Whether checks there is a dependent component of system
     * Проверяет существует ли зависимый компонент системы
     *
     * @param string $componentName , Component name
     * @return bool
     */
    public function _hasComponent($componentName)
    {
        return (isset($this->components->components[$componentName])) ? true : false;
    }

    /**
     * Get component
     *
     * Tries to receive a dependent component of system if that exists
     * Пытается получить зависимый компонент системы, если тот существует
     *
     * @param string $componentName , Component name
     * @return callable|null
     */
    public function _getComponent($componentName)
    {
        return ($this->_hasComponent($componentName)) ? $this->components->components[$componentName] : null;
    }

    /**
     * Call component
     *
     * Tries to call a dependent component of system if that exists
     * Пытается вызвать зависимый компонент системы, если тот существует
     *
     * @param string $componentName , Component name
     * @param array $arguments
     * @return callable|null
     */
    public function _callComponent($componentName, $arguments = [])
    {
        return ($this->_hasComponent($componentName))
            ? call_user_func_array($this->components->components[$componentName], $arguments) : null;
    }

    /**
     * Check core component
     *
     * Whether checks there is a dependent component of a core
     * Проверяет существует ли зависимый компонент ядра
     *
     * @param string $componentName , Component name
     * @return bool
     */
    public function _hasCoreComponent($componentName)
    {
        return (isset($this->components->coreComponents[$componentName])) ? true : false;
    }

    /**
     * Get core component
     *
     * Tries to receive a dependent component of a core if that exists
     * Пытается получить зависимый компонент ядра, если тот существует
     *
     * @param string $componentName , Component name
     * @return callable|null
     */
    public function _getCoreComponent($componentName)
    {
        return ($this->_hasCoreComponent($componentName)) ? $this->components->coreComponents[$componentName] : null;
    }

    /**
     * Call core component
     *
     * Tries to call a dependent component of a core if that exists
     * Пытается вызвать зависимый компонент ядра, если тот существует
     *
     * @param string $componentName , Component name
     * @param array $arguments
     * @return callable|null
     */
    public function _callCoreComponent($componentName, $arguments = [])
    {
        if ($this->_hasCoreComponent($componentName)) {
            if (method_exists($this->components->coreComponents[$componentName][0], 'getInstance')) {
                return call_user_func_array([
                    call_user_func_array([$this->components->coreComponents[$componentName][0], 'getInstance'], []),
                    $this->components->coreComponents[$componentName][1]
                ], $arguments);
            } else {
                return call_user_func_array($this->components->coreComponents[$componentName], $arguments);
            }
        }
        return null;
    }

    /**
     * Check closing component
     *
     * Whether checks there is a dependent closing component
     * Проверяет существует ли замыкаемый компонент ядра
     *
     * @param string $componentName , Component name
     * @return bool
     */
    public function _hasClosingComponent($componentName)
    {
        return (isset($this->closingComponents[$componentName])) ? true : false;
    }

    /**
     * Get closing component
     *
     * Tries to receive a closing component if that exists
     * Пытается получить замыкаемый компонент, если тот существует
     *
     * @param string $componentName , Component name
     * @return callable|null
     */
    public function _getClosingComponent($componentName)
    {
        return ($this->_hasClosingComponent($componentName)) ? $this->closingComponents[$componentName] : null;
    }

    /**
     * Call closing component
     *
     * Tries to call a closing component if that exists
     * Пытается вызвать замыкаемый компонент, если тот существует
     *
     * @param string $componentName , Component name
     * @param array $arguments
     * @return callable|null
     */
    public function _callClosingComponent($componentName, $arguments = [])
    {
        return ($this->_hasClosingComponent($componentName)) ?
            call_user_func_array($this->closingComponents[$componentName], $arguments) : null;
    }

    /**
     * Add component to system
     *
     * @param $name
     * @param $callback
     * @return bool|callable
     */
    public function _registerComponent($name, $callback)
    {
        return (is_callable($callback)) ? $this->components->components[$name] = $callback : false;
    }


    /**
     * Add core component to system
     *
     * @param $name
     * @param $callback
     * @return bool|callable
     */
    public function _registerCoreComponent($name, $callback)
    {
        return (is_callable($callback)) ? $this->components->coreComponents[$name] = $callback : false;
    }

    /**
     * Add closing component to system
     *
     * @param $name
     * @param $callback
     * @return bool|callable
     */
    public function _registerClosingComponent($name, $callback)
    {
        return (is_string($callback) || is_object($callback)) ? $this->closingComponents[$name] = $callback : false;
    }

    /**
     * Delete component from system
     *
     * @param $name
     * @return null
     */
    public function _unRegisterComponent($name)
    {
        return ($this->_hasComponent($name)) ? $this->components->components[$name] = null : null;
    }

    /**
     * Delete core component from system
     *
     * @param $name
     * @return null
     */
    public function _unRegisterCoreComponent($name)
    {
        return ($this->_hasCoreComponent($name)) ? $this->components->coreComponents[$name] = null : null;
    }

    /**
     * Delete closing component from system
     *
     * @param $name
     * @return null
     */
    public function _unRegisterClosingComponent($name)
    {
        return ($this->_hasClosingComponent($name)) ? $this->closingComponents[$name] = null : null;
    }
}
