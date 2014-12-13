<?php namespace mfe;

/**
 * Class TmfeStandardEngineMethods
 * @package mfe
 */
trait TmfeStandardEngineMethods {

    static public function TmfeStandardEngineMethodsInit() {
        mfe::$options = [
            'MFE_PHAR_INIT' => false,
            'MFE_AUTOLOAD' => false,

            'stackObject' => 'mfe\CmfeObjectsStack',
            'FileHelper' => 'mfe\CmfeSimpleFileHelper',
        ];
    }

    /**
     * @param callable $callback
     * @return mfe
     */
    static public function init(\Closure $callback = null) {
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
            $class::trigger('mfe.init');
        }
        return (object)$class::$instance;
    }

    static public function initTraitsBefore() {
        foreach (class_uses(__CLASS__) as $trait) {
            /** @var TmfeStandardEngineMethods $trait */
            $method = (substr($trait, 0, 4) == 'mfe\\') ? substr($trait, 4) . 'Init' : $trait . 'Init';
            if (method_exists($trait, $method)) call_user_func_array([__CLASS__, $method], []);
        }
    }

    public function __initTraitsAfter() {
        foreach (class_uses(__CLASS__) as $trait) {
            /** @var TmfeStandardEngineMethods $trait */
            $method = (substr($trait, 0, 4) == 'mfe\\') ? '__' . substr($trait, 4) . 'Init' : '__' . $trait . 'Init';
            if (method_exists($trait, $method)) call_user_func_array([__CLASS__, $method], []);
        }
    }

    protected function __TmfeStandardEngineMethodsInit() {
        $stackObject = self::option('stackObject');
        foreach (mfe::$register as $stack) {
            $this->$stack = new $stackObject;
        }
    }

    static public function option($option) {
        if (defined($option) && constant($option) == true) {
            return mfe::$options[$option] = true;
        }
        return (isset(mfe::$options[$option])) ? mfe::$options[$option] : null;
    }

    static public function dependence($dependence) {
        if(is_string($dependence)){
            if(!class_exists($dependence)) throw new CmfeException('Not found dependence class: ' . $dependence);
        }
        foreach($dependence as $value){
            if(!class_exists($value)) throw new CmfeException('Not found dependence class: ' . $value);
        }
    }

    public function __toString() {
        return (__TRAIT__) ? __TRAIT__ : __CLASS__;
    }
}
