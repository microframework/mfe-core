<?php namespace mfe;

class CmfeRunHandler {
    static protected $handlers = [
        'server' => [],
        'engine' => [],
        'console' => [],
        'application' => [],
    ];

    static protected $currentHandler = 'server';

    static public function run($handler = null) {
        if (!is_null($handler)) self::$currentHandler = $handler;
        return true;
    }

    static public function errorHandler($error_number, $error_string, $error_file, $error_line) {
        return CmfeDebug::errorHandler([$error_number, $error_string, $error_file, $error_line]);
    }

    static public function fatalErrorHandler() {
        $error = error_get_last();
        switch ($error['type']) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_PARSE:
            CmfeDebug::errorHandler([$error['type'], $error['message'], $error['file'], $error['line']]);
        }
    }

    static public function DebugHandler() {
        return CmfeDebug::displayErrors(self::$currentHandler);
    }

    static public function exceptionHandler(\Exception $e) {
        return CmfeDebug::exceptionHandler($e);
    }
}