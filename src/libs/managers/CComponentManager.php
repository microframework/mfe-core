<?php namespace mfe\core\libs\managers;

use ArrayObject;
use Closure;
use mfe\core\libs\base\CManager;
use mfe\core\libs\components\CException;
use mfe\core\libs\interfaces\IObject;
use mfe\core\libs\system\IoC;
use mfe\core\libs\system\SystemException;

/**
 * Class CComponentManager
 *
 * @pattern ServiceLocator
 * @package mfe\core\libs\managers
 */
class CComponentManager extends CManager implements IObject
{
    const COMPONENT_NAME = 'ComponentManager';
    const COMPONENT_VERSION = '1.0.0';

    /** @var IoC */
    public $IoC;

    /** @var array */
    protected $definitions = [];

    /**
     * @param array $config
     *
     * @return $this
     */
    public function initComponentManager(array $config)
    {
        $this->IoC = new IoC();

        foreach ($config as $key => $value) {
            if ('class' !== $key && 'type' !== $key) {
                $this->$key = $value;
            }
        }
        return $this;
    }

    /**
     *
     */
    public function flushComponentManager()
    {
        $this->flushRegister();
        $this->definitions = [];
    }

    /**
     * @param bool $returnDefinitions
     *
     * @return array|ArrayObject
     */
    public function getComponents($returnDefinitions = true)
    {
        return ($returnDefinitions) ? $this->definitions : $this->getRegister();
    }

    /**
     * @param $components
     *
     * @return $this
     * @throws CException
     */
    public function setComponents($components)
    {
        foreach ($components as $key => $definition) {
            $this->set($key, $definition);
        }
        return $this;
    }

    /**
     * @param string $key
     *
     * @return null
     * @throws SystemException
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->getRegister()) && null !== $this->getRegister()[$key]) {
            return $this->getRegister()[$key];
        }

        if (array_key_exists($key, $this->definitions)) {
            $definition = $this->definitions[$key];
            if (is_object($definition) && !$definition instanceof Closure) {
                return $this->getRegister()[$key] = $definition;
            } else {
                return $this->getRegister()[$key] = $this->buildComponent($definition);
            }
        } else {
            return null;
        }
    }

    /**
     * @param string $key
     * @param bool $checkInstance
     *
     * @return bool
     */
    public function has($key, $checkInstance = false)
    {
        $result = $checkInstance
            ? array_key_exists($key, $this->getRegister())
            : array_key_exists($key, $this->definitions);

        return (true === $result && null === $this->getRegister()[$key]) ? false : (bool)$result;
    }

    /**
     * @param string $key
     * @param object|callable $definition
     *
     * @return bool
     * @throws CException
     */
    public function set($key, $definition)
    {
        if (null === $definition) {
            $this->getRegister()[$key] = $this->definitions[$key] = null;
            return true;
        }

        $this->getRegister()[$key] = null;

        if (is_object($definition) || is_callable($definition, true)) {
            $this->definitions[$key] = $definition;
        } elseif (is_array($definition)) {
            if (array_key_exists('class', $definition)) {
                $this->definitions[$key] = $definition;
                return true;
            } else {
                throw new CException('The config of component must contain a \'class\' key with name class.');
            }
        }

        throw new CException("Unexpected config type for the '{$key}' component: " . gettype($definition));
    }

    /**
     * @param string $key
     * @param array $arguments
     *
     * @return mixed
     * @throws SystemException
     */
    public function call($key, array $arguments)
    {
        return call_user_func_array($this->get($key), $arguments);
    }

    /**
     * @param $definition
     *
     * @return bool|mixed|null
     * @throws SystemException
     */
    private function buildComponent($definition)
    {
        if (is_string($definition)) {
            return $this->IoC->instance(['class' => $definition])->make();
        } elseif (is_array($definition) && array_key_exists('class', $definition)) {
            return (array_key_exists('type', $definition) && IoC::TYPE_SINGLETON === $definition['type'])
                ? $this->IoC->singleton($definition)->make()
                : $this->IoC->instance($definition)->make();
        } elseif (is_callable($definition, true)) {
            return call_user_func($definition);
        }
        return false;
    }
}
