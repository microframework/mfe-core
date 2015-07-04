<?php namespace mfe\core\libs\components;

use Closure;
use mfe\core\libs\interfaces\IEvent;

/**
 * Class CEvent
 * @access private
 *
 * @package mfe\core\libs\managers\local
 */
class CEvent implements IEvent
{
    /** @var string */
    public $name;

    /** @var mixed */
    public $callback;

    /** @var mixed|null */
    public $result;

    /**
     * @param string $name
     * @param mixed $callback
     */
    public function __construct($name, $callback)
    {
        $this->name = $name;
        $this->callback = $callback;
    }

    /**
     * @param array $arguments
     * @return array|mixed
     */
    public function execute(array $arguments = [])
    {
        $result = null;

        if (
            is_callable($this->callback) &&
            ((is_array($this->callback) && 2 === count($this->callback) && is_callable($this->callback))
                || (is_string($this->callback) && is_callable($this->callback))
                || (is_object($this->callback) && ($this->callback instanceof Closure)))
        ) {
            $result = call_user_func_array($this->callback, $arguments);
        }

        $this->result = $result;
        return $this;
    }
}
