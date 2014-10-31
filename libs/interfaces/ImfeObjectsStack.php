<?php namespace mfe;
/**
 * Interface ImfeObjectsStack
 * @package mfe
 */
interface ImfeObjectsStack {
    public function __set($key, $value);

    public function __get($key);

    public function __isset($key);

    public function add($key, $value);

    public function register($value);

    public function override($key, $value);

    public function remove($key);

    public function position($value);

    public function up($count_steps);

    public function down($count_steps);
}
