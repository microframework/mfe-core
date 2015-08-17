<?php namespace mfe\core\api\base;

/**
 * Class IObject
 *
 * @package mfe\core\api\base
 */
interface IObject
{
    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function get($key);

    /**
     * @param string $key
     * @param object $DIObject
     *
     * @return object|null $this
     */
    public function set($key, $DIObject);

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     * @param array $arguments
     *
     * @return mixed
     */
    public function call($key, array $arguments);
}
