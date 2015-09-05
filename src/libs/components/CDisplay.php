<?php namespace mfe\core\libs\components;

use mfe\core\libs\applications\CApplication;
use mfe\core\libs\base\CComponent;

/**
 * Class CDisplay
 *
 * @package mfe\core\libs\components
 */
class CDisplay extends CComponent
{
    const TYPE_DEBUG = 'debug';
    const TYPE_HTML5 = 'html5';
    const TYPE_JSON = 'json';

    /**
     * @param CApplication $application
     * @param string $type
     */
    static public function display(CApplication $application, $type = self::TYPE_HTML5)
    {
        print (string)$application->response->getBody();
    }
}
