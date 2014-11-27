<?php namespace mfe;

class CmfeRunHandler {
    static protected $handlers = [
        'server' => [],
        'engine' => [],
        'console' => [],
        'application' => [],
    ];

    static public function run() {
        return true;
    }

    static public function errorHandler($error_number, $error_string, $error_file, $error_line) {

    }

    static public function fatalErrorHandler() {

    }

    static public function exceptionHandler(\Exception $e) {

    }
}