<?php namespace mfe\core\libs\handlers;

use mfe\core\libs\components\CDebug;

/**
 * Class CRunHandler
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
        if (null !== $handler) self::$currentHandler = $handler;
        return true;
    }

    static public function debugHandler()
    {
        return CDebug::displayErrors(self::$currentHandler);
    }
}
