<?php namespace mfe\core\libs\components;

use Exception;
use mfe\core\mfe;

if (!defined('E_FATAL')) define('E_FATAL', 1);
if (!defined('E_STRICT')) define('E_STRICT', 2048);
if (!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);
if (!defined('E_EXCEPTION')) define('E_EXCEPTION', 5040);

/**
 * Class CDebug
 * @package mfe\core\libs\components
 */
class CDebug
{
    static public $ENABLED = true;

    static protected $errors;
    static protected $trace;

    static public $_CODE = [
        0x00000E0 => 'Catch error',
        //0x00000E1 => 'Engine start events error (engine.start event return false?).',
        0x00000E2 => 'Event return false.',
        0x00000E3 => 'Layout experiences trouble with loading of files.',
        0x00000E4 => 'Catch exception'
    ];

    static public $_ERROR_CODES = [
        E_FATAL => 'Fatal Error',
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse',
        E_NOTICE => 'Notice',
        E_DEPRECATED => 'Deprecated',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_DEPRECATED => 'User Deprecated',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_EXCEPTION => 'Exception'
    ];

    /**
     * @param $code
     * @return bool|null
     */
    static public function criticalStopEngine($code)
    {
        if (0x00000E3 == $code) {
            (!isset($_SERVER['SERVER_PROTOCOL'])) ?:
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        }
        return MfE::stopEngine();
    }

    /**
     * @param $error
     * @return bool|null
     */
    static public function errorHandler($error)
    {
        //$error[0] = $error[0] && call_user_func('error_reporting');
        if (!$error[0]) return false;
        $error[0] = isset(self::$_ERROR_CODES[$error[0]]) ? self::$_ERROR_CODES[$error[0]] : 'Unknown Error';
        if (is_null(self::$trace)) {
            if (function_exists('debug_backtrace')) {
                $backtraceArray = $backtrace = [];
                foreach (call_user_func('debug_backtrace') as $value) {
                    if (isset($value['class'])) $backtrace['class'] = $value['class']; else $backtrace['class'] = null;
                    if (isset($value['type'])) $backtrace['type'] = $value['type']; else $backtrace['type'] = null;
                    $backtrace['function'] = $value['function'];
                    if (isset($value['file'])) $backtrace['file'] = $value['file']; else $backtrace['file'] = null;
                    if (isset($value['line'])) $backtrace['line'] = $value['line']; else $backtrace['line'] = null;
                    if (isset($value['line']) && $value['file'] != __FILE__) {
                        $backtraceArray[] = $backtrace;
                    }
                }
                $error[] = array_reverse($backtraceArray);
            }
        } else {
            $error[] = self::$trace;
        }

        self::$errors[] = $error;

        if ($error[0] === E_EXCEPTION) MfE::stop(0x00000E4);

        return (
            $error[0] === E_FATAL ||
            $error[0] === E_ERROR ||
            $error[0] === E_COMPILE_ERROR
        ) ? MfE::stop(0x00000E0) : null;
    }

    /**
     * @param Exception $e
     * @return bool|null
     */
    static public function exceptionHandler($e)
    {
        self::$trace = $e->getTrace();
        self::errorHandler([5040, 'Exception: ' . $e->getMessage(), $e->getFile(), $e->getLine()]);
        return MfE::stopEngine();
    }

    /**
     * @param $code
     * @return bool|null
     */
    static protected function logAndSplashScreen($code)
    {
        CDebug::display('errorLayout', self::$_CODE[$code]);
        return MfE::stopEngine();
    }

    /**
     * @param $instance
     * @return null
     */
    static public function displayErrors($instance)
    {
        /* Catch Fatal Error */
        $last_error = call_user_func('error_get_last');
        if ($last_error && $last_error['type'] === E_FATAL) {
            self::errorHandler([
                $last_error['type'],
                $last_error['message'],
                $last_error['file'],
                $last_error['line']
            ]);
        }
        /* Catch Fatal Error */

        if (!empty(self::$errors)) {
            //TODO:: fix this to only instance
            if (PHP_SAPI === 'cli' || $instance == 'console') {
                self::cliDisplayErrors();
            } else {
                if (!self::$ENABLED) {
                    self::logAndSplashScreen(500);
                } else {
                    CDebug::display('views.html5.Debug', 500, new \ArrayObject(self::$errors));
                }
            }
        }
        return self::$errors = null;
    }

    static public function cliDisplayErrors()
    {
        $data = 'Debug Console' . PHP_EOL . PHP_EOL;
        $count = 1;
        if (self::$errors) {
            foreach (self::$errors as $error) {
                $data .= '[' . $count++ . ']. [' . $error[0] . '] ' . $error[1] . ' in ' . $error[2] . ' on line ' . $error[3] . PHP_EOL;
                $countStack = 0;
                if ($error[4]) $data .= '  Stack trace:' . PHP_EOL;
                foreach ($error[4] as $value) {
                    if (($value['class'] != '_debug' && $value['class'] != 'debug')) {
                        $data .= '  ' . $countStack++ . '. ' . $value['class'] . $value['type'] . $value['function'] . '()';
                        if ($value['file']) $data .= ' in ' . $value['file'];
                        if ($value['line']) $data .= ' on line ' . $value['line'];
                        $data .= PHP_EOL;
                    }
                }
            }
        }
        print $data;
    }

    static public function renderSourceCode($file, $errorLine, $maxLines)
    {
        $errorLine--;
        if ($errorLine < 0 || ($lines = @file($file)) === false || ($lineCount = count($lines)) <= $errorLine)
            return '';

        $halfLines = (int)($maxLines / 2);
        $beginLine = $errorLine - $halfLines > 0 ? $errorLine - $halfLines : 0;
        $endLine = $errorLine + $halfLines < $lineCount ? $errorLine + $halfLines : $lineCount - 1;
        $lineNumberWidth = strlen($endLine + 1);

        $output = '';
        for ($i = $beginLine; $i <= $endLine; ++$i) {
            $isErrorLine = $i === $errorLine;
            $code = sprintf("<span class=\"ln" . "\">%0{$lineNumberWidth}d</span> %s", $i + 1, str_replace("\t", '    ', $lines[$i]));
            if (!$isErrorLine)
                $output .= $code;
            else
                $output .= '<span class="error">' . $code . '</span>';
        }
        return '<div class="code"><pre>' . $output . '</pre></div>';
    }

    /**
     * @param $layout
     * @param $code
     * @param null $errors
     */
    static protected function display($layout, $code, $errors = null)
    {
        $time = round(microtime(true) - MFE_TIME, 3);

        header('Content-type: text/html; charset=utf-8');
        (!isset($_SERVER['SERVER_PROTOCOL'])) or header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);

        MfE::display((new CLayout($layout, [
            'title' => $code,
            'time' => (($time >= 0.001) ? $time : '0.001') . ' ms',
            'info' => MfE::ENGINE_NAME . ' (' . MfE::ENGINE_VERSION . ')',
            'errors' => $errors
        ]))->render(), CDisplay::TYPE_DEBUG);
    }
}
