<?php namespace mfe\core\libs\traits\standard;

use mfe\core\libs\components\CDebug;
use mfe\core\libs\components\CException;
use mfe\core\libs\helpers\CSimpleFileHelper;
use mfe\core\api\engines\IEngine;
use mfe\core\mfe;

/**
 * Class TStandardEngine
 *
 * @package mfe\core\libs\traits\standard
 */
trait TStandardEngine
{
    static protected $traitsRegister = [];

    /**
     * Trait constructor
     *
     * @throws \mfe\core\libs\components\CException
     */
    protected function __TStandardEngine()
    {
        $stackObject = MfE::getConfigData('utility.StackObject');

        foreach (MfE::$traitsRegister as $stack) {
            $this->$stack = new $stackObject;
        }
    }

    /**
     * @return MfE
     * @throws CException
     */
    static public function createEngine()
    {
        $class = static::class;
        /** @var MfE $class */
        if (null === $class::$instance) {
            self::initTraitsBefore();
            $class::$instance = new $class();
            $class::$instance->__initTraitsAfter();
            $class::$instance->registerComponentManager();
            $class::$instance->startEngine();
        }
        return (object)$class::$instance;
    }

    /**
     * Init behavior trait constructor for all trait in engine
     */
    static public function initTraitsBefore()
    {
        foreach (class_uses(__CLASS__) as $trait) {
            /** @var TStandardEngine $trait */
            $method = (__NAMESPACE__ === substr($trait, 0, strlen(__NAMESPACE__)))
                ? substr($trait, strlen(__NAMESPACE__) + 1)
                : $trait;
            if (method_exists($trait, $method)) {
                call_user_func_array([__CLASS__, $method], []);
            }
        }
    }

    /**
     * Init trait constructor for all trait in engine
     */
    public function __initTraitsAfter()
    {
        foreach (class_uses(__CLASS__) as $trait) {
            /** @var TStandardEngine $trait */
            $method = (__NAMESPACE__ === substr($trait, 0, strlen(__NAMESPACE__)))
                ? '__' . substr($trait, strlen(__NAMESPACE__) + 1)
                : '__' . $trait;
            if (method_exists($trait, $method)) {
                call_user_func_array([__CLASS__, $method], []);
            }
        }
    }

    static protected function begin()
    {
        set_error_handler([CDebug::class, 'errorHandler'], E_ALL);
        set_exception_handler([CDebug::class, 'exceptionHandler']);
        register_shutdown_function([MfE::class, 'stopEngine']);
    }

    /**
     * Print end time
     *
     * @param MfE|IEngine $application
     * @throws \mfe\core\libs\components\CException
     */
    static protected function end(&$application)
    {
        if (!$application::$DEBUG) {
            return;
        }

        /** @var mfe $class */
        $class = static::class;
        /** @var CSimpleFileHelper $FileHelper */
        $FileHelper = MfE::getConfigData('utility.FileHelper');

        $time = round(microtime(true) - MFE_TIME, 3);

        $time = number_format($time, 3) . 's (' . number_format($time * 1000, 0) . 'ms)';
        $memory = ', ' . $FileHelper::convert_size(memory_get_usage(true));

        file_put_contents('php://stdout', PHP_EOL .
            ((!$class::$_STATUS)
                ? 'Done: '
                : ('Error: ' . CDebug::$_CODE[$class::$_STATUS] . ', at ')
            ) . $time . $memory . PHP_EOL
        );
    }

    /**`
     * @return string
     */
    public function __toString()
    {
        return (__TRAIT__) ?: __CLASS__;
    }
}
