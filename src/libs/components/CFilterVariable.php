<?php namespace mfe\core\libs\components;

/**
 * Class CVariable
 *
 * @package mfe\core\libs\components
 */
class CFilterVariable
{
    const FILTER_BOOLEAN = 'filter_boolean';
    const FILTER_INTEGER = 'filter_integer';
    const FILTER_FLOAT = 'filter_float';
    const FILTER_DOUBLE = 'filter_float';
    const FILTER_STRING = 'filter_string';
    const FILTER_ARRAY = 'filter_array';

    private $value;

    public function __construct($value)
    {
        $this->set($value);
    }

    public function __toString()
    {
        return (string)$this->get();
    }

    public function set($value)
    {
        $this->value = $value;
    }

    public function get()
    {
        return $this->value;
    }

    public function filter($filterName, $default = null)
    {

    }
}
