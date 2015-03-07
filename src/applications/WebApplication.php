<?php namespace mfe\core\applications;

use mfe\core\libs\components\CApplication;
use mfe\core\libs\interfaces\IHybridApplication;

/**
 * Class WebApplication
 * @package mfe\core\applications
 */
class WebApplication extends CApplication implements IHybridApplication
{
    const APPLICATION_NAME = 'Standard default application';
    const APPLICATION_TYPE = 'WebApplication';
    const APPLICATION_VERSION = '1.0.0';

    public $result = "Hello World";

    public function run()
    {

    }
}