<?php namespace mfe;

class CmfeDebug {
    static public $ENABLED = false;

    static public $_CODE = [
        0x00000E1 => 'Engine start events error (engine.start event return false?).',
        0x00000E2 => 'Event return false.',
    ];

    static public function criticalStopEngine($code) {
        if (!self::$ENABLED) return self::logAndSplashScreen($code);
        header('Content-type: text/plain; charset=utf-8');
        CmfeDebug::display('errorExtendedLayout', self::$_CODE[$code]);
        return mfe::stopEngine();
    }


    static protected function logAndSplashScreen($code) {
        CmfeLog::error(self::$_CODE[$code]);
        CmfeDebug::display('errorLayout', self::$_CODE[$code]);
        return mfe::stopEngine();
    }

    static protected function display($layout, $message) {

    }
}