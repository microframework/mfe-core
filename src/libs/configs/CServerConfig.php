<?php namespace mfe\core\libs\configs;

use ArrayObject;
use mfe\core\api\configs\IConfig;

/**
 * Class CServerConfig
 *
 * @package mfe\core\libs\configs
 */
class CServerConfig extends ArrayObject implements IConfig
{
    /**
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * @param $string
     *
     * @return static
     */
    public static function fromFile($string)
    {
        $file = $string;

        $config = new static();
        $config->parseConfig($file);

        return $config;
    }

    /**
     * @param $file
     */
    public function parseConfig($file)
    {
    }
}
