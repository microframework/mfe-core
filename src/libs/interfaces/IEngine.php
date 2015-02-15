<?php namespace mfe\core\libs\interfaces;
/**
 * Interface IEngine
 *
 * This interface dictates rules of writing of engines for MicroFramework
 * Этот интерфейс диктует правила написания двигателей для MicroFramework
 *
 * @standards MFS-4.1, MFS-5.[1,2]
 * @package mfe
 */
interface IEngine
{
    static function init();

    static function app($applicationName);

    static function trigger($eventName);

    static function on($eventName, $callback);

    static function off($eventName, $callback = null);

    static function registerComponent($name, $callback, $core = false, $override = false);

    static function overrideComponent($name, $callback = null, $core = false);

    static function unRegisterComponent($name, $core = false);
}
