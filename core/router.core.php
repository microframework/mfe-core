<?php namespace mfe;
/**
 * Class RouterCore
 * @package mfe
 */

mfe::dependence('request');

class Router extends CCore implements IComponent {
    const CORE_COMPONENT_NAME = 'Router';
    const CORE_COMPONENT_VERSION = '1.0.0';

    public $route = '';
    protected $routePaths = [];

    static public $mainRoute = '';

    static public function registerComponent() {
        mfe::registerCoreComponent('router', [get_called_class(), 'routerInit']);
        return true;
    }

    public function routerInit() {
        $this->route = '';
    }
}
