<?php namespace mfe;

class CmfeLog {
    /** @var CmfeLog $instance */
    static private $instance = null;

    public function __construct() {

    }

    public function addToLog($type, $message, $backtrace) {

    }

    static protected function init() {
        $class = get_called_class();
        /** @var CmfeLog $class */
        if (is_null($class::$instance)) {
            $class::$instance = new $class();
            mfe::trigger('system.log.init');
        }
        return (object)$class::$instance;
    }

    static public function error($message, $backtrace = false) {
        if (is_null(self::$instance)) self::init();
        self::$instance->addToLog('error', $message, $backtrace);
    }
}