<?php namespace mfe\core\applications;

use mfe\core\libs\base\CApplication;
use mfe\core\libs\interfaces\applications\IHybridApplication;

/**
 * Class WebApplication
 * @package mfe\core\applications
 */
class WebApplication extends CApplication implements IHybridApplication
{
    const APPLICATION_NAME = 'Standard default application';
    const APPLICATION_TYPE = 'WebApplication';
    const APPLICATION_VERSION = '1.0.0';

    /** @constant string, Please not modify! */
    const APPLICATION_DIR = __DIR__;

    public $result = "Hello World";
}
