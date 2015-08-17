<?php namespace mfe\core\api\stack;
/**
 * Interface IObjectsStack
 *
 * @package mfe\core\api\stack
 */
interface IObjectsStack
{
    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function __set($key, $value);

    /**
     * @param $key
     *
     * @return mixed
     */
    public function __get($key);

    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function add($key, $value);

    /**
     * @param $key
     *
     * @return mixed
     */
    public function remove($key);

    /**
     * @param $value
     *
     * @return mixed
     */
    public function position($value);

    /**
     * @param $key
     * @param int $count_steps
     *
     * @return mixed
     */
    public function up($key, $count_steps = 1);

    /**
     * @param $key
     * @param int $count_steps
     *
     * @return mixed
     */
    public function down($key, $count_steps = 1);

    /**
     * @return mixed
     */
    public function flush();
}
