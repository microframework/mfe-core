<?php namespace mfe\core\libs\handlers;

use mfe\core\libs\components\CDebug;
use mfe\core\libs\components\CDisplay;
use mfe\core\MfE;

/**
 * Class CRunHandler
 *
 * @package mfe\core\libs\handlers
 */
class CRunHandler
{
    static protected $handlers = [
        'server' => [],
        'engine' => [],
        'console' => [],
        'application' => [],
    ];

    static protected $currentHandler = 'server';

    static public function run($handler = null)
    {
        if (null !== $handler) {
            self::$currentHandler = $handler;
        }

        if (!MFE_SERVER) {
            MfE::app()->events->on('application.run', function () {
                $application = MfE::app();
                $application->request = CRequestFactory::fromGlobals();
                $application->events->trigger('application.request', [$application]);

                CDisplay::display($application, CDisplay::TYPE_HTML5, 'utf-8');
            });
        }

        return true;
    }

    static public function debugHandler()
    {
        return CDebug::displayErrors(self::$currentHandler);
    }
}
