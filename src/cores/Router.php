<?php namespace mfe\core\cores;

use mfe\core\libs\base\CCore;

/**
 * Class Router
 *
 * @deprecated
 * @package mfe\core\cores
 */
class Router extends CCore
{
    const COMPONENT_NAME = 'Router';
    const COMPONENT_VERSION = '1.0.0';

    public $route = '';
    protected $routePaths = [];

    static public $mainRoute = '';

    public function routerInit()
    {
        $this->route = '';
    }
}
