<?php namespace mfe\core\libs\traits\standard;

use mfe\core\libs\base\CApplication;
use mfe\core\libs\components\CDebug;
use mfe\core\libs\components\CDisplay;
use mfe\core\libs\components\CException;
use mfe\core\libs\components\CObjectsStack;
use mfe\core\Init;
use mfe\core\mfe;

/**
 * Class TStandardApplication
 *
 * @package mfe\core\libs\traits\standard
 */
trait TStandardApplication
{
    public $currentApplication;

    /** @var CObjectsStack */
    protected $applications;
    static public $_STATUS = 0x0000000;


    /**
     * Behavior trait constructor
     */
    static public function TStandardApplication()
    {
        mfe::$register['TR'][] = 'applications';
    }

    /**
     * TODO:: Application stack
     *
     * @param Init $config
     * @return mfe
     */
    static public function app(Init $config = null)
    {
        if (!count(mfe::getInstance()->applications)) return mfe::getInstance();
        return mfe::getInstance()->applications->{mfe::getInstance()->currentApplication};
    }

    /**
     * @param CApplication $application
     * @param bool $setAsCurrentApplication
     * @return bool
     * @throws \Exception
     */
    public function registerApplication(CApplication $application, $setAsCurrentApplication = true)
    {
        if ($setAsCurrentApplication) $this->currentApplication = get_class($application);
        mfe::getInstance()->applications->add(get_class($application), $application);
        return true;
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
    static public function display($data)
    {
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