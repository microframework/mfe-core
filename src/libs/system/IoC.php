<?php namespace mfe\core\libs\system;

use mfe\core\libs\interfaces\IIoC;
use Exception;

/**
 * Class IoC
 * @package mfe\core\libs\system
 */
class IoC implements IIoC
{
    private $container = [];

    /**
     * @param $key
     * @throws Exception
     * @return mixed|null
     */
    public function __get($key){
        if($this->has($key)) {
            return $this->get($key);
        }
        throw new Exception('Getting unknown property: '. $key);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        return array_key_exists($key, $this->container) ? $this->container : null;
    }

    /**
     * @param $key
     * @param $DIObject
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
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->container) ? true : false;
    }

    /**
     * @param $key
     * @param $arguments
     * @return mixed
     */
    public function call($key, $arguments)
    {
        if ($this->has($key) && is_callable($this->container[$key])) {
            return call_user_func_array($this->container[$key], $arguments);
        }
        return null;
    }
}
