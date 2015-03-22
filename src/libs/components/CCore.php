<?php namespace mfe\core\libs\components;

use mfe\core\libs\interfaces\IComponent;
use mfe\core\mfe;

/**
 * Class CCore
 * @package mfe\core\libs\components
 */
abstract class CCore extends CComponent implements IComponent
{
    static public $instance;

    static public function getInstance()
    {
        if (is_null(static::$instance)) {
            /** @var CCore $class */
            $class = static::class;
            static::$instance = new $class();
        }
        return static::$instance;
    }

    /**
     * @param $option
     * @return bool|null
     */
    static protected function option($option)
    {
        return mfe::option($option);
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
     * @return mixed
     * @throws CException
     */
    public function __set($key, $value)
    {
        return parent::__set($key, $value);
    }

    /**
     * @param $key
     * @return mixed
     * @throws CException
     */
    public function __get($key)
    {
        return parent::__get($key);
    }
}
