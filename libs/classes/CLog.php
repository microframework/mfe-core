<?php namespace mfe;

class CLog {
    /** @var CLog $instance */
    static private $instance = null;

    public function __construct() {

    }

    public function addToLog($type, $message, $backtrace) {

    }

    static protected function getInstance() {
        $class = get_called_class();
        /** @var CLog $class */
        if (is_null($class::$instance)) {
            $class::$instance = new $class();
            mfe::trigger('system.log.init');
        }
        return (object)$class::$instance;
    }

    static public function error($message, $backtrace = false) {
        if (is_null(self::$instance)) self::getInstance();
        self::$instance->addToLog('error', $message, $backtrace);
    }
}