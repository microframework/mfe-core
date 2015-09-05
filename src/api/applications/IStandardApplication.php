<?php namespace mfe\core\api\applications;

/**
 * Interface IStandardApplication
 *
 * @package mfe\core\api\applications
 */
interface IStandardApplication
{
    public function setup();

    public function main();

    public function run();
}
