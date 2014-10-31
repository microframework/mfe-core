<?php namespace mfe;
/**
 * Interface ImfeEvents
 * rr * @eng_desc This Interface dictates coding rules for events manager of MFE
 * @rus_desc Этот интерфейс диктует правила написания менеджера событий для MFE
 *
 * @standards MFS-5.6
 * @package mfe
 */
interface ImfeEventsManager {
    function registerEvent($event_node);

    function addEvent($event_node, $callback);

    function removeEvent($event_node, $callback);

    function fireEvent($event_node);

    function clearEvent($event_node);
}
