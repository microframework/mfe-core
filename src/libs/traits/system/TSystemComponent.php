<?php namespace mfe\core\libs\traits\system;

/**
 * Class TSystemComponent
 * @package mfe\core\libs\traits\system
 */
trait TSystemComponent
{
    /**
     * @deprecated please use ::class
     * @return string
     */
    static public function className()
    {
        return (string)static::class;
    }
} 
