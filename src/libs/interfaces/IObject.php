<?php namespace mfe\core\libs\interfaces;

/**
 * Class IObject
 * @package mfe\core\libs\system
 */
interface IObject
{
    /**
     * @param $key
     * @return mixed|null
     */
    public function get($key);

    /**
     * @param $key
     * @param $DIObject
     * @return $this
     */
    public function set($key, $DIObject);

    /**
     * @param $key
     * @return bool
     */
    public function has($key);

    /**
     * @param $key
     * @param $arguments
     * @return mixed
     */
    public function call($key, $arguments);
}
