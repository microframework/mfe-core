<?php namespace mfe\Loader;

use mfe\CCore;
use mfe\IComponent;
use mfe\mfe as engine;
use mfe\TStandardLoader;

class Loader extends CCore implements IComponent {
    use TStandardLoader;

    const CORE_COMPONENT_NAME = 'Loader';
    const CORE_COMPONENT_VERSION = '1.0.0';

    public function __construct() {
        $stack = engine::option('stackObject');

        $this->aliases = new $stack();
        $this->filesMap = new $stack(engine::app()->getFilesMap());

        foreach (engine::app()->getAliases() as $alias => $array) {
            foreach ($array as $value) {
                $this->registerAliasDirectory($alias, $value);
            }
        }

        $this->registerLoader();
    }

    protected function registerLoader($undo = false) {
        /** @var Loader $class */
        $class = get_called_class();

        $components = [
            'registerAlias' => [$class, '_registerAlias'],
            'loadFile' => [$class, '_loadFile'],
            'loadPhar' => [$class, '_loadPhar'],
            'loadCore' => [$class, '_loadCore'],
            'loadMapFile' => [$class, '_loadMapFile'],
            'map' => [$class, '_map'],
            'loadMap' => [$class, '_loadMap'],
        ];

        foreach ($components as $key => $callback) {
            (!$undo) ? engine::app()->registerCoreComponent($key, $callback)
                : engine::app()->unRegisterCoreComponent($key);
        }

        return $components;
    }

    static public function registerComponent() {
        engine::registerComponent('loader', [get_called_class(), 'getInstance']);
        return true;
    }
}
