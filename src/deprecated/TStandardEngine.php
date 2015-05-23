<?php namespace mfe\core\deprecated;

use mfe\core\libs\components\CDebug;
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
        $stackObject = mfe::option('stackObject');
        foreach (mfe::$register['TR'] as $stack) {
            $this->$stack = new $stackObject;
        }
    }

    /**
     * @return mfe
     */
    static public function getInstance()
    {
        $class = static::class;
        /** @var mfe $class */
        if (is_null($class::$instance)) {
            self::initTraitsBefore();
            $class::$instance = new $class();
            $class::$instance->__initTraitsAfter();
            //$class::$instance->on('mfe.init', function () {
            call_user_func_array([mfe::$instance, 'startEngine'], []);
            //});
            //$class::$instance->trigger('mfe.init');
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
        $class = static::class;
        /** @var CSimpleFileHelper $FileHelper */
        $FileHelper = $class::option('FileHelper');

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
        return (__TRAIT__) ? __TRAIT__ : __CLASS__;
    }
}
