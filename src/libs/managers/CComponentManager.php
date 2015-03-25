<?php namespace mfe\core\libs\managers;

use Closure;
use mfe\core\libs\base\CComponent;
use mfe\core\libs\base\CManager;

/**
 * Class CComponentManager
 *
 * @method static add(string $name, mixed $object, string $method = 'getInstance')
 * @method static remove(string $name)
 *
 * @package mfe\core\libs\managers
 */
class CComponentManager extends CManager
{
    const COMPONENT_NAME = 'ComponentManager';
    const COMPONENT_VERSION = '1.0.0';

    /**
     * @param string $name
     * @param CComponent|Closure|string $object
     * @param string $method
     */
    protected function callAdd($name, $object, $method = 'getInstance')
    {
        $this->getRegister()[$name] = [$object, $method];
    }

    /**
     * @param string $name
     */
    protected function callRemove($name)
    {
        $this->getRegister()[$name] = null;
    }
}
