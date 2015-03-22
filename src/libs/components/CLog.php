<?php namespace mfe\core\libs\components;

/**
 * Class CLog
 *
 * @method static CLog getInstance()
 *
 * @package mfe\core\libs\components
 */
class CLog extends CComponent
{
    /** @var CLog */
    static public $instance;

    /**
     * @const
     */
    const EMERGENCY = 'Emergency';
    const ALERT = 'Alert';
    const CRITICAL = 'Critical';
    const ERROR = 'Error';
    const WARNING = 'Warning';
    const NOTICE = 'Notice';
    const INFO = 'Info';
    const DEBUG = 'Debug';

    public function __construct()
    {
        return self::$instance;
    }

    protected function addToLog($type, $message, $backtrace)
    {
        $log_file = fopen('mfe.log', 'a'); //TODO:: refactor this!
        fwrite($log_file, '[' . $type . ']: ' . $message . '.' . PHP_EOL . '    Trace start:' . PHP_EOL);
        $numb = 0;
        foreach (($backtrace == false ? array_reverse(call_user_func('debug_backtrace')) : array_reverse($backtrace)) as $value) {
            if (!isset($value['class'])) $value['class'] = null;
            if (!isset($value['type'])) $value['type'] = null;
            if (!isset($value['file'])) $value['file'] = null;
            if (!isset($value['line'])) $value['line'] = null;
            fwrite($log_file, '    ' . $numb++ . '. in function ' . $value['class'] . $value['type'] . $value['function'] .
                ' in ' . $value['file'] . ' on line ' . $value['line'] . PHP_EOL);
        }
        fclose($log_file);
    }

    /**
     * @param $message
     * @param bool $backtrace
     */
    public function _emergency($message, $backtrace = false)
    {
        $this->addToLog(self::EMERGENCY, $message, $backtrace);
    }

    public function _alert($message, $backtrace = false)
    {
        $this->addToLog(self::ALERT, $message, $backtrace);
    }

    public function _critical($message, $backtrace = false)
    {
        $this->addToLog(self::CRITICAL, $message, $backtrace);
    }

    public function _error($message, $backtrace = false)
    {
        $this->addToLog(self::ERROR, $message, $backtrace);
    }

    public function _warning($message, $backtrace = false)
    {
        $this->addToLog(self::WARNING, $message, $backtrace);
    }

    public function _notice($message, $backtrace = false)
    {
        $this->addToLog(self::NOTICE, $message, $backtrace);
    }

    public function _info($message, $backtrace = false)
    {
        $this->addToLog(self::INFO, $message, $backtrace);
    }

    public function _debug($message, $backtrace = false)
    {
        $this->addToLog(self::DEBUG, $message, $backtrace);
    }

}
