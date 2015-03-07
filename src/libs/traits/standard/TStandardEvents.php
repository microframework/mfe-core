<?php namespace mfe\core\libs\traits\standard;

use mfe\core\libs\traits\application\TApplicationEvents;
use mfe\core\libs\components\CException;
use mfe\core\libs\components\CObjectsStack;
use mfe\core\mfe;

/**
 * Class TStandardEvents
 *
 * @property mixed components
 * @property mixed event
 * @property mixed eventManager
 *
 * @method static bool trigger
 * @method static bool on
 * @method static bool off
 *
 * @package mfe\core\libs\traits\standard
 */
trait TStandardEvents
{
    /** @var CObjectsStack */
    protected $eventsMap;

    use TApplicationEvents;

    /**
     * Behavior trait constructor
     */
    static public function TStandardEvents()
    {
        mfe::$register[] = 'eventsMap';
    }

    /**
     * Trait constructor
     */
    protected function __TStandardEvents()
    {
        /** @var mfe $class */
        $class = get_called_class();

        $this->registerStandardEvents();

        $class::registerClosingComponent('events', $class);
        $class::registerClosingComponent('eventsManager', $class);
    }

    protected function registerStandardEvents($undo = false)
    {
        /** @var mfe $class */
        $class = get_called_class();

        $components = [
            'trigger' => [$class, '_trigger'],
            'on' => [$class, '_on'],
            'off' => [$class, '_off']
        ];

        foreach ($components as $key => $callback) {
            (!$undo) ? $class::registerCoreComponent($key, $callback)
                : $class::unRegisterCoreComponent($key);
        }

        return $components;
    }

    /**
     * @param $event_node
     * @return bool
     */
    public function registerEvent($event_node)
    {
        if (!is_string($event_node)) return false;
        $this->trigger('event.register', [$event_node]);

        if (!isset($this->eventsMap[$event_node])) {
            /** @var mfe $class */
            $class = get_called_class();
            $stack = $class::option('stackObject');

            $this->eventsMap[$event_node] = new $stack();
        }
        return true;
    }

    /**
     * @param $event_node
     * @param $callback
     * @return bool
     */
    public function addEvent($event_node, $callback)
    {
        if (!is_string($event_node)) return false;
        $this->trigger('event.add', [$event_node, $callback]);

        if (!isset($this->eventsMap[$event_node])) $this->registerEvent($event_node);
        $this->eventsMap[$event_node][] = $callback;

        return true;
    }

    /**
     * @param $event_node
     * @param $callback
     * @return bool
     */
    public function removeEvent($event_node, $callback)
    {
        if (!is_string($event_node)) return false;
        $this->trigger('event.remove', [$event_node, $callback]);

        if (!isset($this->eventsMap[$event_node])) return true;
        $key = array_search($callback, $this->eventsMap[$event_node]);
        if ($key || $key === 0) unset($this->eventsMap[$event_node][$key]);

        return true;
    }

    /**
     * @param $event_node
     * @param array $params
     * @return bool
     * @throws CException
     */
    public function fireEvent($event_node, $params = [])
    {
        if (!is_string($event_node)) return false;
        if ($event_node !== 'event.fire') $this->trigger('event.fire', [$event_node, $params]);

        if (!isset($this->eventsMap[$event_node])) return null;
        foreach ($this->eventsMap[$event_node] as $event) {
            if (is_object($event) && is_callable($event)) {
                // TODO:: Fix second param, to link with stats object
                if ($event($params, mfe::app()) === false) {
                    throw new CException("Event \r\n" . print_r($event, true) . "\r\n return false", 0x00000E2);
                }
            } elseif (is_string($event) && isset($this->eventsMap[$event]) && $event_node !== $event) {
                if ($this->trigger($event) === false) {
                    throw new CException("Event \r\n{$event}\r\n return false", 0x00000E2);
                }
            }
        }

        return true;
    }

    /**
     * @param $event_node
     * @return bool
     */
    public function clearEvent($event_node)
    {
        if (!is_string($event_node)) return false;
        $this->trigger('event.clear', [$event_node]);

        if (!isset($this->eventsMap[$event_node])) return true;
        $this->eventsMap[$event_node] = [];

        return false;
    }
}
