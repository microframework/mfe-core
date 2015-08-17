<?php namespace mfe\core\api\events;

/**
 * Interface IEvent
 *
 * @property string $name
 * @property mixed $callback
 * @property mixed|null $result
 *
 * @package mfe\core\api\events
 */
interface IEvent {
    /**
     * @param array $arguments
     *
     * @return mixed
     */
    public function execute(array $arguments = []);
}
