<?php namespace mfe\core\applications;

use mfe\core\api\applications\IHybridApplication;
use mfe\core\libs\applications\CApplication;

/**
 * Class DefaultApplication
 *
 * @package mfe\core\applications
 */
class DefaultApplication extends CApplication implements IHybridApplication
{
    const APPLICATION_NAME = 'Default application';
    const APPLICATION_TYPE = 'HybridApplication';
    const APPLICATION_VERSION = '1.0.0';

    /** @constant string, Please not modify! */
    const APPLICATION_DIR = __DIR__;

    /**
     * {@inheritDoc}
     */
    public function main()
    {
        echo 'Hello World' . PHP_EOL;
    }
}
