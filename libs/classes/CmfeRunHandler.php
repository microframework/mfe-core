<?php namespace mfe;

class CmfeRunHandler {
    static protected $handlers = [
        'server' => [],
        'engine' => [],
        'console' => [],
        'application' => [],
    ];

    static public function run(){
        return true;
    }
}