<?php namespace mfe\core\libs\base;

use mfe\core\libs\components\CException;
use mfe\core\libs\system\Object;
use mfe\core\libs\system\TSystemComponent;

/**
 * Class CComponent
 *
 * @package mfe\core\libs\base
 */
abstract class CComponent extends Object
{
    const COMPONENT_NAME = 'Default component';
    const COMPONENT_VERSION = '1.0.0';

    use TSystemComponent;

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
     *
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
     *
     * @return mixed
     * @throws CException
     */
    public function __get($key)
    {
        if (parent::has($key)) {
            return parent::get($key); // Object
        }

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
     *
     * @return mixed
     * @throws CException
     */
    public function __call($method, array $arguments = [])
    {
        if (method_exists($this, 'call' . ucfirst($method)) &&
            (new \ReflectionObject($this))->getMethod('call' . ucfirst($method))->isProtected()
        ) {
            return call_user_func_array([$this, 'call' . ucfirst($method)], $arguments);
        }

        throw new CException('Try to get unknown method: ' . $method);
    }
}
