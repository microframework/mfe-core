<?php namespace mfe;

class CmfeDebug {
    static public $_CODE = [
        0 => 'Engine start events error.',
    ];

    static public function criticalStopEngine($code){
        header('Content-type: text/plain; charset=utf-8');
        print self::$_CODE[$code];
    }
}