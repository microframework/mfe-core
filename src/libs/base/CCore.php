<?php namespace mfe\core\libs\base;

use mfe\core\libs\components\CException;
use mfe\core\api\components\IComponent;
use mfe\core\mfe;

/**
 * Class CCore
 *
 * @package mfe\core\libs\base
 */
abstract class CCore extends CComponent implements IComponent
{
    /**
     * @param $option
     *
     * @return bool|null
     * @throws CException
     */
    static protected function option($option)
    {
        //TODO:: Refactor this
        return MfE::getConfigData('options'. $option);
    }

    /**
     * @return bool
     */
    static public function registerComponent()
    {
        return true;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     * @throws CException
     */
    public function __set($key, $value)
    {
        return parent::__set($key, $value);
    }

    /**
     * @param $key
     *
     * @return mixed
     * @throws CException
     */
    public function __get($key)
    {
        return parent::__get($key);
    }
}
