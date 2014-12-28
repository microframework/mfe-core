<?php namespace mfe;
/**
 * Interface IObjectsStack
 * @package mfe
 */
interface IObjectsStack {
    public function __set($key, $value);

    public function __get($key);

    public function add($key, $value);

    public function remove($key);

    public function position($value);

    public function up($key, $count_steps = 1);

    public function down($key, $count_steps = 1);

    public function flush();
}
