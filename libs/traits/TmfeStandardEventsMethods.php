<?php namespace mfe;

/**
 * Class TmfeStandardEventsMethods
 *
 * @property mixed components
 * @property mixed event
 * @property mixed eventManager
 *
 * @package mfe
 */
trait TmfeStandardEventsMethods {
    /** @var CmfeObjectsStack */
    protected $eventsMap = null;

    public function registerEvent($event_node) {
        if (!is_string($event_node)) return false;
        self::trigger('event.register', [$event_node]);
        if (isset($this->components->components['events'])) {
            return $this->event->registerEvent($event_node);
        } elseif (isset($this->components->components['eventsManager'])) {
            return $this->eventManager->registerEvent($event_node);
        } else {
            if (!isset($this->eventsMap[$event_node]))
                $class = get_called_class();
                /** @var mfe $class */
                $stack = $class::option('stackObject');
                $this->eventsMap[$event_node] = new $stack();
        }
        return true;
    }

    public function addEvent($event_node, $callback) {
        if (!is_string($event_node)) return false;
        self::trigger('event.add', [$event_node, $callback]);
        if (!isset($this->eventsMap[$event_node])) $this->registerEvent($event_node);
        if (isset($this->components->components['events'])) {
            return $this->event->addEvent($event_node, $callback);
        } elseif (isset($this->components->components['eventsManager'])) {
            return $this->eventManager->addEvent($event_node, $callback);
        } else {
            $this->eventsMap[$event_node][] = $callback;
        }
        return true;
    }

    public function removeEvent($event_node, $callback) {
        if (!is_string($event_node)) return false;
        self::trigger('event.remove', [$event_node, $callback]);
        if (!isset($this->eventsMap[$event_node])) return true;
        if (isset($this->components->components['events'])) {
            return $this->event->removeEvent($event_node, $callback);
        } elseif (isset($this->components->components['eventsManager'])) {
            return $this->eventManager->removeEvent($event_node, $callback);
        } else {
            $key = array_search($callback, $this->eventsMap[$event_node]);
            if ($key || $key === 0) unset($this->eventsMap[$event_node][$key]);
            return true;
        }
    }

    public function fireEvent($event_node, $params = []) {
        if (!is_string($event_node)) return false;
        if ($event_node !== 'event.fire') self::trigger('event.fire', [$event_node, $params]);
        if (isset($this->components->components['events'])) {
            return $this->event->fireEvent($event_node);
        } elseif (isset($this->components->components['eventsManager'])) {
            return $this->eventManager->fireEvent($event_node);
        } else {
            if (!isset($this->eventsMap[$event_node])) return false;
            foreach ($this->eventsMap[$event_node] as $event) {
                if (is_object($event) && is_callable($event)) {
                    // TODO:: Fix second param, to link with stats object
                    $event($params, mfe::init());
                } elseif (is_string($event)
                    && isset($this->eventsMap[$event])
                    && $event_node !== $event
                ) {
                    self::trigger($event);
                }
            }
        }
        return true;
    }

    public function clearEvent($event_node) {
        if (!is_string($event_node)) return false;
        self::trigger('event.clear', [$event_node]);
        if (isset($this->components->components['events'])) {
            return $this->event->clearEvent($event_node);
        } elseif (isset($this->components->components['eventsManager'])) {
            return $this->eventManager->clearEvent($event_node);
        } else {
            if (!isset($this->eventsMap[$event_node])) return true;
            $this->eventsMap[$event_node] = [];
        }
        return false;
    }



    static public function trigger($event, $params = []) {
        $class = get_called_class();
        /** @var mfe $class */
        $class::init()->fireEvent($event, $params);
    }

    static public function on($event, $callback) {
        $class = get_called_class();
        /** @var mfe $class */
        $class::init()->addEvent($event, $callback);
    }

    static public function off($event, $callback = null) {
        $class = get_called_class();
        /** @var mfe $class */
        if (is_null($callback)) {
            $class::init()->clearEvent($event);
        } else $class::init()->removeEvent($event, $callback);
    }
}
