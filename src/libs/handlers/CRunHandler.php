<?php namespace mfe\core\libs\handlers;

use mfe\core\libs\components\CDebug;
use mfe\core\libs\components\CDisplay;
use mfe\core\libs\http\CResponse;
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
            MfE::app()->set('request', CRequestFactory::fromGlobals());
            MfE::app()->set('response', new CResponse());
            MfE::app()->events->on('application.run', function () {
                MfE::app()->events->trigger('application.request', [
                    MfE::app()->request,
                    MfE::app()->response
                ]);
                MfE::app()->events->trigger('application.response', [
                    MfE::app()->request,
                    MfE::app()->response
                ]);
                CDisplay::display(MfE::app()->response, CDisplay::TYPE_HTML5, 'utf-8');
            });
        }

        return true;
    }

    static public function debugHandler()
    {
        return CDebug::displayErrors(self::$currentHandler);
    }
}
