<?php namespace mfe;

/**
 * Class TmfeStandardEngineMethods
 * @package mfe
 */
trait TmfeStandardEngineMethods {
    /** @var array */
    static public $options = [
        'MFE_PHAR_INIT' => false,
        'MFE_AUTOLOAD' => false,

        'stackObject' => 'mfe\CmfeObjectsStack',
        'FileHelper' => 'mfe\CmfeSimpleFileHelper',
    ];

    static public function init(\Closure $callback = null) {
        $class = get_called_class();
        /** @var mfe $class */
        if (is_null($class::$instance)) {
            $class::$instance = new $class();
            if (is_callable($callback))
                $callback();
            $class::trigger('mfe.init');
        }
        return (object)$class::$instance;
    }

    static public function option($option) {
        if (defined($option) && constant($option) == true) {
            return self::$options[$option] = true;
        }
        return (isset(self::$options[$option])) ? self::$options[$option] : null;
    }
}