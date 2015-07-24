<?php namespace mfe\core\libs\system;

use mfe\core\libs\interfaces\IObject;

/**
 * Class Object
 * @package mfe\core\libs\system
 */
class Object implements IObject
{
    private $container = [];

    /**
     * @param string $key
     *
     * @return mixed|null
     * @throws SystemException
     */
    public function __get($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }
        throw new SystemException('Getting unknown property: ' . $key);
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        return array_key_exists($key, $this->container) ? $this->container[$key] : null;
    }

    /**
     * @param string $key
     * @param object $DIObject
     *
     * @return $this
     */
    public function set($key, $DIObject)
    {
        if (is_object($DIObject)) {
            $this->container[$key] = $DIObject;
        }
        return $this;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->container) ? true : false;
    }

    /**
     * @param string $key
     * @param array $arguments
     *
     * @return mixed
     */
    public function call($key, array $arguments)
    {
        if ($this->has($key) && is_callable($this->container[$key])) {
            return call_user_func_array($this->container[$key], $arguments);
        }
        return null;
    }

    /**
     * @param $container
     */
    protected function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    protected function getContainer()
    {
        return $this->container;
    }
}
