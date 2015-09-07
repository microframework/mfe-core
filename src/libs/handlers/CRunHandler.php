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
        'console' => [],
        'application' => [],
    ];

    static protected $currentHandler = 'application';

    static public function run($handler = null)
    {
        if (null !== $handler) {
            self::$currentHandler = $handler;
        }

        switch (self::$currentHandler) {
            case 'application':
                MfE::app()->events->on('application.run', function () {
                    $application = MfE::app();
                    $application->request = CRequestFactory::fromGlobals();

                    ob_start();
                    $bufferLevel = ob_get_level();
                    $application->events->trigger('application.request', [$application]);

                    CDisplay::display($application, CDisplay::TYPE_EMITTER_SAPI, $bufferLevel);
                });
                break;
        }

        return true;
    }

    static public function debugHandler()
    {
        return CDebug::displayErrors(self::$currentHandler);
    }
}
