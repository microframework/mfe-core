<?php namespace mfe;
/**
 * Class RouterCore
 * @package mfe
 */

mfe::dependence('request');

class RouterCore implements ImfeComponent {
    const CORE_COMPONENT_NAME = 'RouterCore';
    const CORE_COMPONENT_VERSION = '1.0.0';

    public $route = '';
    protected $routePaths = [];

    static public $mainRoute = '';

    static public function registerComponent() {
        return true;
    }

    public function routerInit() {
        $this->route = '';
    }
}

mfe::registerCoreComponent('router', [get_called_class(), 'routerInit']);