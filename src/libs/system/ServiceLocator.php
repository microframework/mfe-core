<?php namespace mfe\core\libs\system;

use Closure;
use mfe\core\libs\components\CException;
use mfe\core\MfE;

/**
 * Class ServiceLocator
 *
 * @package mfe\core\libs\system
 */
class ServiceLocator extends Object
{
    public $components = [];
    public $definitions = [];

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->components)) {
            return $this->components[$key];
        }

        if (array_key_exists($key, $this->definitions)) {
            $definition = $this->definitions[$key];
            if (is_object($definition) && !$definition instanceof Closure) {
                return $this->components[$key] = $definition;
            } else {
                return $this->components[$key] = $this->build($definition);
            }
        } else {
            return null;
        }
    }

    public function has($key, $checkInstance = false)
    {
        return $checkInstance ? array_key_exists($key, $this->components) : array_key_exists($key, $this->definitions);
    }

    public function set($key, $definition)
    {
        if ($definition === null) {
            $this->components[$key] = $this->definitions[$key];
            return;
        }

        $this->components[$key] = null;

        if (is_object($definition) || is_callable($definition, true)) {
            $this->definitions[$key] = $definition;
        } elseif (is_array($definition)) {
            if (array_key_exists('class', $definition)) {
                $this->definitions[$key] = $definition;
            } else {
                throw new CException('The configuration component must contain a <class> element.');
            }
        }

        throw new CException("Unexpected configuration type for the <{$key}> component: " . gettype($definition));
    }

    public function flush()
    {
        $this->components = $this->definitions = [];
    }

    public function getComponents($returnDefinitions = true)
    {
        return ($returnDefinitions) ? $this->definitions : $this->components;
    }

    public function setComponents($components)
    {
        foreach ($components as $key => $definition) {
            $this->set($key, $definition);
        }
    }

    private function build($definition, array $params = [])
    {
        if (is_string($definition)) {
            return MfE::app()->get('container')->get($definition, $params);
        } elseif (is_array($definition) && array_key_exists('class', $definition)) {
            $class = $definition['class'];
            $definition['class'] = null;
            return MfE::app()->get('container')->get($class, $params, $definition);
        } elseif (is_callable($definition, true)) {
            return call_user_func($definition, $params);
        }
        return false;
    }
}
