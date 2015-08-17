<?php namespace mfe\core\api\applications;

/**
 * Interface IConsoleApplication
 *
 * @package mfe\core\api\application
 */
interface IConsoleApplication extends IStandardApplication
{
    /**
     * @return void
     */
    public function run();
}
