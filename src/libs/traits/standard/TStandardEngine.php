<?php namespace mfe\core\libs\traits\standard;

use mfe\core\libs\components\CDebug;
use mfe\core\libs\components\CException;
use mfe\core\libs\helpers\CSimpleFileHelper;
use mfe\core\mfe;

/**
 * Class TStandardEngine
 *
 * @package mfe\core\libs\traits\standard
 */
trait TStandardEngine
{
    /**
     * Behavior trait constructor
     */
    static public function TStandardEngine()
    {
        mfe::$options = [
            'MFE_PHAR_INIT' => false,
            'MFE_AUTOLOAD' => false,

            'stackObject' => 'mfe\core\libs\components\CObjectsStack',
            'FileHelper' => 'mfe\core\libs\helpers\CSimpleFileHelper',
        ];
    }

    /**
     * Trait constructor
     */
    protected function __TStandardEngine()
    {
        $stackObject = self::option('stackObject');
        foreach (mfe::$register as $stack) {
            $this->$stack = new $stackObject;
        }
    }

    /**
     * @return mfe
     */
    static public function getInstance()
    {
        $class = get_called_class();
        /** @var mfe $class */
        if (is_null($class::$instance)) {
            self::initTraitsBefore();
            $class::$instance = new $class();
            $class::$instance->__initTraitsAfter();
            $class::$instance->on('mfe.init', function () {
                call_user_func_array([mfe::$instance, 'startEngine'], []);
            });
            $class::$instance->trigger('mfe.init');
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
            $method = (substr($trait, 0, strlen(__NAMESPACE__)) == __NAMESPACE__)
                ? substr($trait, strlen(__NAMESPACE__) + 1)
                : $trait;
            if (method_exists($trait, $method)) call_user_func_array([__CLASS__, $method], []);
        }
    }

    /**
     * Init trait constructor for all trait in engine
     */
    public function __initTraitsAfter()
    {
        foreach (class_uses(__CLASS__) as $trait) {
            /** @var TStandardEngine $trait */
            $method = (substr($trait, 0, strlen(__NAMESPACE__)) == __NAMESPACE__)
                ? '__' . substr($trait, strlen(__NAMESPACE__) + 1)
                : '__' . $trait;
            if (method_exists($trait, $method)) call_user_func_array([__CLASS__, $method], []);
        }
    }

    /**
     * Print end time
     */
    static protected function end()
    {
        /** @var mfe $class */
        $class = get_called_class();
        /** @var CSimpleFileHelper $FileHelper */
        $FileHelper = $class::option('FileHelper');
        $time = round(microtime(true) - MFE_TIME, 3);

        $s = (($time >= 0.001) ? $time : '0.001') . 's';
        $ms = ' (' . (($time >= 0.001) ? $time * 1000 : '1') . 'ms)';
        $memory = ', ' .$FileHelper::convert_size(memory_get_usage(true));

        file_put_contents('php://stdout', PHP_EOL .
            ((!$class::$_STATUS) ? 'Done: ' : 'Error: ' . CDebug::$_CODE[$class::$_STATUS] . ', at ') .
            ($s . $ms . $memory . PHP_EOL));
    }

    /**
     * TODO:: WHERE is needed?
     *
     * @return string
     */
    public function __toString()
    {
        return (__TRAIT__) ? __TRAIT__ : __CLASS__;
    }
}
