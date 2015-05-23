<?php namespace mfe\core\cores;

use mfe\core\libs\interfaces\IComponent;

use mfe\core\libs\base\CCore;
use mfe\core\mfe;

//mfe::dependence('request');

/**
 * Class Router
 * @package mfe\core\cores
 */
class Router extends CCore implements IComponent
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

    static public function registerComponent()
    {
        mfe::registerCoreComponent('router', [static::class, 'routerInit']);
        return true;
    }
}
