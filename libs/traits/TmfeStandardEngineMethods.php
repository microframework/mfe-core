<?php namespace mfe;

/**
 * Class TmfeStandardEngineMethods
 * @package mfe
 */
trait TmfeStandardEngineMethods {

    protected $time;

    static public function TmfeStandardEngineMethodsInit(){
        mfe::$options = [
            'MFE_PHAR_INIT' => false,
            'MFE_AUTOLOAD' => false,

            'stackObject' => 'mfe\CmfeObjectsStack',
            'FileHelper' => 'mfe\CmfeSimpleFileHelper',
        ];
    }

    static public function init(\Closure $callback = null) {
        $class = get_called_class();
        /** @var mfe $class */
        if (is_null($class::$instance)) {
            self::initTraits();
            $class::$instance = new $class();
            if (is_callable($callback))
                $callback();
            $class::trigger('mfe.init');
        }
        return (object)$class::$instance;
    }

    static public function initTraits(){
        foreach (class_uses(__CLASS__) as $trait) {
            /** @var TmfeStandardEngineMethods $trait */
            $method = (substr($trait, 0, 4) == 'mfe\\') ? substr($trait, 4) . 'Init' : $trait . 'Init';
            if (method_exists($trait, $method)) call_user_func_array([__CLASS__, $method], []);
        }
    }

    protected function initRegister($stackObject = null){
        if(is_null($stackObject)) $stackObject = self::option('stackObject');
        foreach(mfe::$register as $stack){
            $this->$stack = new $stackObject;
        }
    }

    static public function option($option) {
        if (defined($option) && constant($option) == true) {
            return mfe::$options[$option] = true;
        }
        return (isset(mfe::$options[$option])) ? mfe::$options[$option] : null;
    }

    public function __toString(){
        return (__TRAIT__) ? __TRAIT__ : __CLASS__;
    }
}
