<?php namespace mfe\core\libs\traits;

use mfe\core\libs\components\CDebug;
use mfe\core\libs\components\CDisplay;
use mfe\core\libs\components\CException;
use mfe\core\libs\components\CObjectsStack;
use mfe\core\mfe;

/**
 * Class TStandardApplication
 *
 * @package mfe\core\libs\traits
 */
trait TStandardApplication
{
    /** @var CObjectsStack */
    protected $applications = null;
    static public $_STATUS = 0x0000000;

    /**
     * Behavior trait constructor
     */
    static public function TStandardApplication()
    {
        mfe::$register[] = 'applications';
    }

    /**
     * TODO:: Application stack
     *
     * @param mixed|null $id
     * @return mfe
     */
    static public function app($id = null)
    {
        return mfe::getInstance();
    }

    /**
     * @param $error_code
     * @return bool|null
     */
    static public function stop($error_code)
    {
        self::$_STATUS = $error_code;
        return CDebug::criticalStopEngine($error_code);
    }

    /**
     * @param $data
     */
    static public function display($data) {
        CDisplay::display($data);
    }

    /**
     * TODO:: This is for applicationManager()
     *
     * @param $arguments
     */
    public function __invoke($arguments)
    {

    }


    /** Заглушки */

//    final public function __debugInfo() {
//        return [MFE_VERSION];
//    }

//    final static public function __set_state($array) {
//        //return [MFE_VERSION];
//    }

    final public function __clone()
    {
        throw new CException('mfe can\'t be cloned');
    }

    final public function __sleep()
    {
        throw new CException('mfe can\'t be serialized');
    }

    final public function __wakeup()
    {
        throw new CException('mfe can\'t be serialized');
    }
}
