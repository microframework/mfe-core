<?php namespace mfe;

class CmfeDebug {
    static public $_CODE = [
        0x00000E1 => 'Engine start events error (engine.start event return false?).',
    ];

    static public function criticalStopEngine($code){
        header('Content-type: text/plain; charset=utf-8');
        print self::$_CODE[$code];
        mfe::stopEngine();
    }
}