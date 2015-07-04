<?php namespace mfe\core\libs\traits\application;

use mfe\core\libs\components\CException;
use mfe\core\mfe;

/**
 * Class TApplicationEngine
 * @package mfe\core\libs\traits\application
 */
trait TApplicationEngine
{
    static public $options = [];

    /**
     * @param $path
     * @return null
     * @throws CException
     */
    static public function getConfigData($path)
    {
        $params = explode('.', $path);
        $result = null;
        $return_false = false;
        foreach ($params as $key) {
            if ($key === 'options' || $key === 'params') {
                $return_false = true;
            }

            $config = (null === $result) ? MfE::$config : $result;

            if (array_key_exists($key, $config)) {
                $result = $config[$key];
            } elseif ($return_false) {
                return false;
            } else {
                //TODO:: более удобный вид array.array.array
                throw new CException('Config Error, not found ' . $path);
            }
        }

        return $result;
    }

    /**
     * @param $option
     * @return null|bool|mixed
     * @throws CException
     */
    public function getOption($option)
    {
        return self::getConfigData('options.' . $option);
    }

    /**
     * @param $param
     * @return null|bool|mixed
     * @throws CException
     */
    public function getParam($param)
    {
        return self::getConfigData('params.' . $param);
    }
}
