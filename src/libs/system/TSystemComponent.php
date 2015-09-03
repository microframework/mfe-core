<?php namespace mfe\core\libs\system;

/**
 * Class TSystemComponent
 *
 * @package mfe\core\libs\system
 */
trait TSystemComponent
{
    /**
     * @deprecated Please use ::class
     * @return string
     */
    static public function className()
    {
        return (string)static::class;
    }
} 
