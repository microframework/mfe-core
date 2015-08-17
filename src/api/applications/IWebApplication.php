<?php namespace mfe\core\api\applications;

/**
 * Interface IWebApplication
 *
 * @package mfe\core\api\applications
 */
interface IWebApplication extends IStandardApplication
{
    /**
     * @return void
     */
    public function run();
}
