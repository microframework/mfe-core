<?php namespace mfe\core\api\events\managers;

use Closure;

/**
 * Interface IEvents
 *
 * This Interface dictates coding rules for events manager of MFE
 * Этот интерфейс диктует правила написания менеджера событий для MFE
 *
 * @method static IEventsManager on(string $eventName, mixed $callback)
 * @method static IEventsManager off(string $eventName, mixed $callback)
 * @method static IEventsManager trigger(string $eventName, array $arguments = [], Closure $callback = null)
 *
 * @standards MFS-5.6
 * @package mfe\core\api\events\managers
 */
interface IEventsManager
{
    /**
     * @param $event_node
     *
     * @return mixed
     */
    function registerEvent($event_node);

    /**
     * @param $event_node
     * @param $callback
     *
     * @return mixed
     */
    function addEvent($event_node, $callback);

    /**
     * @param $event_node
     * @param $callback
     *
     * @return mixed
     */
    function removeEvent($event_node, $callback);

    /**
     * @param $event_node
     *
     * @return mixed
     */
    function fireEvent($event_node);

    /**
     * @param $event_node
     *
     * @return mixed
     */
    function clearEvent($event_node);
}
