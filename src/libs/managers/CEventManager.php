<?php namespace mfe\core\libs\managers;

use ArrayObject;
use Closure;
use mfe\core\libs\components\CComponent;
use mfe\core\libs\components\CEvent;
use mfe\core\libs\components\CException;
use mfe\core\libs\interfaces\IEvent;
use mfe\core\mfe;

/**
 * Class CEventManager
 *
 * @method static CEventManager on(string $eventName, mixed $callback)
 * @method static CEventManager off(string $eventName, mixed $callback)
 * @method static CEventManager trigger(string $eventName, array $arguments, Closure $callback)
 *
 * @package mfe\core\libs\managers
 */
class CEventManager extends CComponent
{
    const COMPONENT_NAME = 'EventManager';
    const COMPONENT_VERSION = '1.0.0';

    /** @var ArrayObject */
    private $localRegister;

    /** @var ArrayObject */
    private $globalRegister;

    /** @var CEventManager */
    static public $instance;

    public function __construct()
    {
        if (class_exists('mfe\core\mfe') && $stackObject = mfe::app()->option('stackObject')) {
            $this->localRegister = new $stackObject();
        } else {
            $this->localRegister = new ArrayObject();
        }

        self::$instance;
    }

    /**
     * @return ArrayObject
     */
    protected function getRegister()
    {
        if (null !== $this->globalRegister) {
            return $this->globalRegister;
        }
        return $this->localRegister;
    }

    /**
     * @param ArrayObject $register
     * @return $this
     */
    public function setRegister(ArrayObject $register)
    {
        $registerClass = get_class($register);
        $this->globalRegister = new $registerClass(array_merge((array)$register, (array)$this->localRegister));
        return $this;
    }

    /**
     * @param mixed $callback
     * @return null|string
     * @throws CException
     */
    protected function callbackHash($callback){
        if(!is_callable($callback)){
            throw new CException('Not valid callback for Event');
        }

        if ((is_object($callback) && ($callback instanceof Closure || $callback instanceof IEvent))) {
            return (string) md5(spl_object_hash($callback));
        } elseif (is_array($callback) && 2 == count($callback)) {
            return  md5(implode(';', $callback));
        } elseif (is_string($callback)) {
            return  md5($callback);
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
     */
    protected function callOn($eventName, $callback)
    {
        if (!isset($this->getRegister()[$eventName])) {
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
     */
    protected function callOff($eventName, $callback)
    {
        if (!isset($this->getRegister()[$eventName])) {
            return $this;
        }

        $hash = $this->callbackHash($callback);

        if (isset($this->getRegister()[$eventName][$hash])) {
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
    protected function callTrigger($eventName, $arguments = [], Closure $callback = null)
    {
        if (!isset($this->getRegister()[$eventName]) || 0 == count($this->getRegister()[$eventName])) {
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
