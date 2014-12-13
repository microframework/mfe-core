<?php namespace mfe;

class CmfeDebug {
    static public $ENABLED = true;

    static protected $errors;

    static public $_CODE = [
        0x00000E0 => null,
        0x00000E1 => 'Engine start events error (engine.start event return false?).',
        0x00000E2 => 'Event return false.',
        0x00000E3 => 'Layout experiences trouble with loading of files.',
        0x00000E4 => 'Exception?!'
    ];

    static public function criticalStopEngine($code) {
        if ($code == 0x00000E3) {
            (!isset($_SERVER['SERVER_PROTOCOL'])) ?:
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            exit;
        }
        return mfe::stopEngine();
    }

    static public function errorHandler($error) {
        $error[0] = $error[0] & error_reporting();
        if (!$error[0]) return false;
        if (!defined('E_FATAL')) define('E_FATAL', 1);
        if (!defined('E_STRICT')) define('E_STRICT', 2048);
        if (!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);
        if (!defined('E_EXCEPTION')) define('E_EXCEPTION', 5040);
        switch ($error[0]) {
            case E_FATAL:
                $data[] = 'Fatal Error';
                break;
            case E_ERROR:
                $data[] = 'Error';
                break;
            case E_WARNING:
                $data[] = 'Warning';
                break;
            case E_PARSE:
                $data[] = 'Parse';
                break;
            case E_NOTICE:
                $data[] = 'Notice';
                break;
            case E_DEPRECATED:
                $data[] = 'Deprecated';
                break;
            case E_CORE_ERROR:
                $data[] = 'Core Error';
                break;
            case E_CORE_WARNING:
                $data[] = 'Core Warning';
                break;
            case E_COMPILE_ERROR:
                $data[] = 'Compile Error';
                break;
            case E_COMPILE_WARNING:
                $data[] = 'Compile Warning';
                break;
            case E_USER_ERROR:
                $data[] = 'User Error';
                break;
            case E_USER_WARNING:
                $data[] = 'User Warning';
                break;
            case E_USER_DEPRECATED:
                $data[] = 'User Deprecated';
                break;
            case E_USER_NOTICE:
                $data[] = 'User Notice';
                break;
            case E_STRICT:
                $data[] = 'Strict Notice';
                break;
            case E_RECOVERABLE_ERROR:
                $data[] = 'Recoverable Error';
                break;
            case E_EXCEPTION:
                $data[] = 'Exception';
                break;
            default:
                $data[] = 'Unknown Error';
        }
        $data[] = $error[1];
        $data[] = $error[2];
        $data[] = $error[3];
        if (function_exists('debug_backtrace')) {
            $backtraceArray = $backtrace = [];
            $array = debug_backtrace();
            foreach ($array as $value) {
                if (isset($value['class'])) $backtrace['class'] = $value['class']; else $backtrace['class'] = null;
                if (isset($value['type'])) $backtrace['type'] = $value['type']; else $backtrace['type'] = null;
                $backtrace['function'] = $value['function'];
                if (isset($value['file'])) $backtrace['file'] = $value['file']; else $backtrace['file'] = null;
                if (isset($value['line'])) $backtrace['line'] = $value['line']; else $backtrace['line'] = null;
                $backtraceArray[] = $backtrace;
            }
            $data[] = array_reverse($backtraceArray);
        }
        self::$errors[] = $data;

        return ($error[0] === E_FATAL) ? mfe::stop(0x00000E0) : null;
    }

    static public function exceptionHandler(\Exception $e) {
        if (!self::$ENABLED) return self::logAndSplashScreen(0x00000E4);
        CmfeDebug::display('errorExtendedLayout', self::$_CODE[0x00000E4], $e);
        return mfe::stopEngine();
    }

    static protected function logAndSplashScreen($code) {
        CmfeLog::error(self::$_CODE[$code]);
        CmfeDebug::display('errorLayout', self::$_CODE[$code]);
        return mfe::stopEngine();
    }

    static public function displayErrors($instance) {
        if (!empty(self::$errors)) {
            //TODO:: fix this to only instance
            if (PHP_SAPI === 'cli' || $instance == 'console') {
                self::cliDisplayErrors();
            } else {
                if (!self::$ENABLED) {
                    self::logAndSplashScreen(500);
                } else {
                    CmfeDebug::display('errorExtendedLayout', 500, new \ArrayObject(self::$errors));
                }
            }
        }
        return self::$errors = null;
    }

    static public function cliDisplayErrors() {
        $data = 'Debug Console' . PHP_EOL . PHP_EOL;
        $count = 1;
        if (self::$errors) {
            foreach (self::$errors as $error) {
                $data .= '[' . $count++ . ']. [' . $error[0] . '] ' . $error[1] . ' in ' . $error[2] . ' on line ' . $error[3] . PHP_EOL;
                $countStack = 0;
                if ($error[4]) $data .= '  Trace start:' . PHP_EOL;
                foreach ($error[4] as $value) {
                    if (($value['class'] != '_debug' && $value['class'] != 'debug')) {
                        $data .= '  ' . $countStack++ . '. in function ' . $value['class'] . $value['type'] . $value['function'] . '()';
                        if ($value['file']) $data .= ' in ' . $value['file'];
                        if ($value['line']) $data .= ' on line ' . $value['line'];
                        $data .= PHP_EOL;
                    }
                }
            }
        }
        print $data;
    }

    static protected function display($layout, $code, $errors = null) {
        $time = round(microtime(true) - MFE_TIME, 3);

        header('Content-type: text/html; charset=utf-8');
        (!isset($_SERVER['SERVER_PROTOCOL'])) ?: header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);

        print new CmfeLayout($layout, [
            'title' => $code,
            'time' => (($time >= 0.001) ? $time : '0.001') . ' ms',
            'info' => 'MicroFramework Engine (' . MFE_VERSION . ')',
            'errors' => $errors
        ]);
    }
}
