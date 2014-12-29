<?php namespace mfe;
/**
 * Class RouterCore
 * @package mfe
 */

mfe::dependence('request');

class Router extends CCore implements IComponent {
    const COMPONENT_NAME = 'Router';
    const COMPONENT_VERSION = '1.0.0';

    public $route = '';
    protected $routePaths = [];

    static public $mainRoute = '';

    public function routerInit() {
        $this->route = '';
    }

    static public function registerComponent() {
        mfe::registerCoreComponent('router', [get_called_class(), 'routerInit']);
        return true;
    }
}
