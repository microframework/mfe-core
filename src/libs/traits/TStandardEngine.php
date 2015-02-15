<?php namespace mfe\core\libs\traits;

use mfe\core\libs\components\CException;
use mfe\core\mfe;

/**
 * Class TStandardEngine
 *
 * @package mfe\core\libs\traits
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
            'FileHelper' => 'mfe\core\libs\components\CSimpleFileHelper',
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
     * @param callable $callback
     * @return mfe
     */
    static public function getInstance(\Closure $callback = null)
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
            if (is_callable($callback))
                $callback();
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
     * @param $option
     * @return bool|null
     */
    static public function option($option)
    {
        if (defined($option) && constant($option) == true) {
            return mfe::$options[$option] = true;
        }
        return (isset(mfe::$options[$option])) ? mfe::$options[$option] : null;
    }

    /**
     * @param $dependence
     * @throws CException
     */
    static public function dependence($dependence)
    {
        if (is_string($dependence)) {
            if (!class_exists($dependence, false)) throw new CException('Not found dependence class: ' . $dependence);
        } elseif (is_array($dependence) && !empty($dependence)) {
            foreach ($dependence as $value) {
                if (!class_exists($value, false)) throw new CException('Not found dependence class: ' . $value);
            }
        }
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
