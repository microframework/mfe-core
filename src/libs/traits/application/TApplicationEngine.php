<?php namespace mfe\core\libs\traits\application;

use mfe\core\libs\components\CApplication;
use mfe\core\libs\components\CException;
use mfe\core\mfe;

/**
 * Class TApplicationEngine
 * @package mfe\core\libs\traits\application
 */
trait TApplicationEngine
{
    static public $options = [];

    public function getRegister($registerName)
    {
        if (array_search($registerName, mfe::$register) !== false) {
            return $this->$registerName;
        }
        return null;
    }

    /**
     * @param $option
     * @return bool|null
     */
    public function getOption($option)
    {
        /** @var mfe|CApplication $class */
        $class = get_called_class();

        $options = $class::$options;
        if (isset($options[$option])) {
            return $options[$option];
        }

        if (defined($option)) {
            return constant($option);
        }
        return null;
    }

    /**
     * @param $option
     * @return bool|null
     */
    static public function option($option)
    {
        return mfe::app()->getOption($option);
    }

    /**
     * @param $dependence
     * @throws CException
     */
    static public function dependence($dependence)
    {
        if (is_string($dependence)) {
            if (!class_exists($dependence, false)) throw new CException('Not found dependence class: ' . $dependence);
        } elseif (is_array($dependence) && !empty($dependence)) {
            foreach ($dependence as $value) {
                if (!class_exists($value, false)) throw new CException('Not found dependence class: ' . $value);
            }
        }
    }
}
