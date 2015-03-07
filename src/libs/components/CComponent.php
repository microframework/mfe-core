<?php namespace mfe\core\libs\components;

/**
 * Class CComponent
 * @package mfe\core\libs\components
 */
abstract class CComponent
{
    const COMPONENT_NAME = 'Default component';
    const COMPONENT_VERSION = '1.0.0';

    /** @var CComponent */
    static private $instance;

    /**
     * @return CComponent
     */
    static public function getInstance()
    {
        if (is_null(self::$instance)) {
            /** @var CComponent $class */
            $class = get_called_class();
            self::$instance = new $class();
        }
        return self::$instance;
    }

    /**
     * @return string
     */
    static public function getMetaInfo()
    {
        /** @var CComponent $class */
        $class = get_called_class();

        return json_encode([
            'name' => $class::COMPONENT_NAME,
            'version' => $class::COMPONENT_VERSION
        ]);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     * @throws CException
     */
    public function __set($key, $value)
    {
        if (method_exists($this, 'set' . ucfirst($key)) && (new \ReflectionObject($this))->getMethod('set' . ucfirst($key))->isPublic()) {
            return $this->{'set' . ucfirst($key)}();
        }

        throw new CException('Try to set unknown property: ' . $key);
    }

    /**
     * @param $key
     * @return mixed
     * @throws CException
     */
    public function __get($key)
    {
        if (method_exists($this, 'get' . ucfirst($key)) && (new \ReflectionObject($this))->getMethod('get' . ucfirst($key))->isPublic()) {
            return $this->{'get' . ucfirst($key)}();
        }

        throw new CException('Try to get unknown property: ' . $key);
    }
}