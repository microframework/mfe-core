<?php namespace mfe;
/**
 * Class TmfeStandardApplicationMethods
 * @package mfe
 */
trait TmfeStandardApplicationMethods {
    /** @var CmfeObjectsStack */
    protected $applications = null;


    //TODO:: Application stack
    static public function app($id = null) {
        $class = get_called_class();
        /** @var mfe $class */
        $class::init();
        return $id;
    }

    //TODO:: This is for applicationManager()
    public function __invoke($arguments) {

    }




    /** Заглушки */

    final public function __debugInfo() {
        return [MFE_VERSION];
    }

    final public function __toString() {
        return MFE_VERSION;
    }

    final static public function __set_state($array) {
        return [MFE_VERSION];
    }

    final public function __clone() {
        throw new CmfeException('mfe can\'t be cloned');
    }

    final public function __sleep() {
        throw new CmfeException('mfe can\'t be serialized');
    }

    final public function __wakeup() {
        throw new CmfeException('mfe can\'t be serialized');
    }
}
