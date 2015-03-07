<?php namespace mfe\core\libs\traits\application;

use mfe\core\libs\components\CException;

/**
 * Class TApplicationEvents
 * @package mfe\core\libs\traits\application
 */
trait TApplicationEvents
{
    /**
     * @param $event
     * @param array $params
     * @return bool
     * @throws CException
     */
    public function _trigger($event, $params = [])
    {
        return $this->fireEvent($event, $params);
    }

    /**
     * @param $event
     * @param $callback
     * @return bool
     */
    public function _on($event, $callback)
    {
        return $this->addEvent($event, $callback);
    }

    /**
     * @param $event
     * @param null $callback
     * @return bool
     */
    public function _off($event, $callback = null)
    {
        return (is_null($callback)) ? $this->clearEvent($event) : $this->removeEvent($event, $callback);
    }
}