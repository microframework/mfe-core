<?php namespace mfe\core\libs\mvc;

use ArrayObject;

/**
 * Class CBaseView
 *
 * @package mfe\core\libs\mvc
 */
abstract class CBaseView
{
    /** @var ArrayObject|array */
    protected $config;

    /** @var CBaseController */
    protected $controller;
}
