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
        return CmfeDebug::errorHandler([$error['type'], $error['message'], $error['file'], $error['line']]);
    }

    static public function DebugHandler() {
        return CmfeDebug::displayErrors(self::$currentHandler);
    }

    static public function exceptionHandler(\Exception $e) {
        return CmfeDebug::exceptionHandler($e);
    }
}