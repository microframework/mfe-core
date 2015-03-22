<?php namespace mfe\core\libs\interfaces;

/**
 * Interface IEvent
 *
 * @property string $name
 * @property mixed $callback
 * @property mixed|null $result
 *
 * @package mfe\core\libs\interfaces
 */
interface IEvent {
    /**
     * @param array $arguments
     * @return mixed
     */
    public function execute(array $arguments = []);
}
