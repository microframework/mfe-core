<?php namespace mfe\core\libs\components;
use mfe\core\libs\system\IoC;

/**
 * Class CComponent
 * @package mfe\core\libs\components
 */
abstract class CComponent extends IoC
{
    const COMPONENT_NAME = 'Default component';
    const COMPONENT_VERSION = '1.0.0';

    /** @var CComponent */
    static public $instance;

    /**
     * @return CComponent
     */
    static public function getInstance()
    {
        if (is_null(static::$instance)) {
            /** @var CComponent $class */
            $class = static::class;
            static::$instance = new $class();
        }
        return static::$instance;
    }

    /**
     * @return string
     */
    static public function className()
    {
        return (string)static::class;
    }

    /**
     * @return string
     */
    static public function getMetaInfo()
    {
        /** @var CComponent $class */
        $class = static::class;

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
        if (method_exists($this, 'set' . ucfirst($key)) &&
            (
                (new \ReflectionObject($this))->getMethod('set' . ucfirst($key))->isPublic() ||
                (new \ReflectionObject($this))->getProperty($key)->isProtected()
            )
        ) {
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
        if(parent::has($key)) return parent::get($key); //IoC

        if (method_exists($this, 'get' . ucfirst($key)) &&
            (
                (new \ReflectionObject($this))->getMethod('get' . ucfirst($key))->isPublic() ||
                (new \ReflectionObject($this))->getProperty($key)->isProtected()
            )
        ) {
            return $this->{'get' . ucfirst($key)}();
        }

        throw new CException('Try to get unknown property: ' . $key);
    }

    /**
     * @param $method
     * @param array $arguments
     * @return mixed
     * @throws CException
     */
    public function __call($method, $arguments = [])
    {
        if (method_exists($this, 'call' . ucfirst($method)) &&
            (new \ReflectionObject($this))->getMethod('call' . ucfirst($method))->isProtected()
        ) {
            return call_user_func_array([$this, 'call' . ucfirst($method)], $arguments);
        }

        throw new CException('Try to get unknown method: ' . $method);
    }

    /**
     * @param $method
     * @param array $arguments
     * @return mixed
     * @throws CException
     */
    static public function __callStatic($method, $arguments = [])
    {
        if (method_exists(self::getInstance(), 'call' . ucfirst($method)) &&
            (new \ReflectionObject(self::getInstance()))->getMethod('call' . ucfirst($method))->isProtected()
        ) {
            return call_user_func_array([self::getInstance(), 'call' . ucfirst($method)], $arguments);
        }

        throw new CException('Try to get unknown static method: ' . $method);
    }
}
