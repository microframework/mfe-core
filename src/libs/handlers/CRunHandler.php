<?php namespace mfe\core\libs\handlers;

use Exception;
use mfe\core\libs\components\CDebug;
use mfe\core\libs\components\CLog;

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

    static public function errorHandler($error_number, $error_string, $error_file, $error_line)
    {
        CLog::getInstance()->_error($error_string . ' in ' . $error_file . ' on line ' . $error_line);
        return CDebug::errorHandler([$error_number, $error_string, $error_file, $error_line]);
    }

    static public function fatalErrorHandler()
    {
        $error = error_get_last();
        switch ($error['type']) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_PARSE:
                self::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    static public function DebugHandler()
    {
        return CDebug::displayErrors(self::$currentHandler);
    }

    static public function exceptionHandler(Exception $e)
    {
        return CDebug::exceptionHandler($e);
    }
}
