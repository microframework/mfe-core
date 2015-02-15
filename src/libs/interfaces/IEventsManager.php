<?php namespace mfe\core\libs\interfaces;
/**
 * Interface IEvents
 *
 * This Interface dictates coding rules for events manager of MFE
 * Этот интерфейс диктует правила написания менеджера событий для MFE
 *
 * @standards MFS-5.6
 * @package mfe
 */
interface IEventsManager
{
    function registerEvent($event_node);

    function addEvent($event_node, $callback);

    function removeEvent($event_node, $callback);

    function fireEvent($event_node);

    function clearEvent($event_node);
}
