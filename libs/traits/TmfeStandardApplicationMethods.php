<?php namespace mfe;
/**
 * Class TmfeStandardApplicationMethods
 * @package mfe
 */
trait TmfeStandardApplicationMethods {
    /** @var CmfeObjectsStack */
    protected $applications = null;
    static public $_STATUS = 0x0000000;

    static public function TmfeStandardApplicationMethodsInit() {
        mfe::$register[] = 'applications';
    }

    //TODO:: Application stack
    /**
     * @param mixed|null $id
     * @return mfe
     */
    static public function app($id = null) {
        return mfe::init();
    }

    static public function stop($error_code){
        self::$_STATUS = $error_code;
        return CmfeDebug::criticalStopEngine($error_code);
    }

    //TODO:: This is for applicationManager()
    public function __invoke($arguments) {

    }


    /** Заглушки */

    final public function __debugInfo() {
        return [MFE_VERSION];
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
