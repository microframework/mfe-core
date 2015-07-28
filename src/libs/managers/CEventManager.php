<?php namespace mfe\core\libs\managers;

use Closure;
use mfe\core\libs\base\CManager;
use mfe\core\libs\components\CEvent;
use mfe\core\libs\components\CException;
use mfe\core\libs\interfaces\IEvent;

/**
 * Class CEventManager
 *
 * @method static CEventManager on(string $eventName, mixed $callback)
 * @method static CEventManager off(string $eventName, mixed $callback)
 * @method static CEventManager trigger(string $eventName, array $arguments = [], Closure $callback = null)
 *
 * @package mfe\core\libs\managers
 */
class CEventManager extends CManager
{
    const COMPONENT_NAME = 'EventManager';
    const COMPONENT_VERSION = '1.0.0';

    /**
     * @param mixed $callback
     * @return null|string
     * @throws CException
     */
    protected function callbackHash($callback)
    {
        if (!is_callable($callback)) {
            throw new CException('Not valid callback for Event');
        }

        if ((is_object($callback) && ($callback instanceof Closure || $callback instanceof IEvent))) {
            return (string)md5(spl_object_hash($callback));
        } elseif (is_array($callback) && 2 === count($callback)) {
            return md5(implode(';', $callback));
        } elseif (is_string($callback)) {
            return md5($callback);
        }

        return null;
    }

    /**
     * @name On
     * @static
     *
     * @param $eventName
     * @param $callback
     * @return $this
     * @throws CException
     */
    protected function callOn($eventName, $callback)
    {
        if (!array_key_exists($eventName, $this->getRegister())) {
            $this->getRegister()[$eventName] = [];
        }

        $hash = $this->callbackHash($callback);

        if ($callback instanceof IEvent) {
            $this->getRegister()[$eventName][$hash] = $callback;
        } else {
            $this->getRegister()[$eventName][$hash] = new CEvent($eventName, $callback);
        }

        return $this;
    }

    /**
     * @name Off
     * @static
     *
     * @param $eventName
     * @param $callback
     * @return $this
     * @throws CException
     */
    protected function callOff($eventName, $callback)
    {
        if (!array_key_exists($eventName, $this->getRegister())) {
            return $this;
        }

        $hash = $this->callbackHash($callback);

        if (array_key_exists($hash, $this->getRegister()[$eventName])) {
            $this->getRegister()[$eventName][$hash] = null;
        }

        return $this;
    }

    /**
     * @name Trigger
     * @static
     *
     * @param string $eventName
     * @param array $arguments
     * @param Closure|null $callback
     *
     * @throws CException
     * @return mixed|null
     */
    protected function callTrigger($eventName, array $arguments = [], Closure $callback = null)
    {
        if (!array_key_exists($eventName, $this->getRegister()) || 0 === count($this->getRegister()[$eventName])) {
            return $this;
        }
        foreach ($this->getRegister()[$eventName] as $hash => $event) {
            if (null !== $event) {
                /** @var IEvent $event */
                $event->execute($arguments);

                if (null !== $callback) {
                    $callback($event);
                }
            }
        }
        return $this;
    }
}
