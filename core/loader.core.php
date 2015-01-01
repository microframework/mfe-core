<?php namespace mfe\Loader;

use mfe\mfe as engine;

class LoaderCore {
    const CORE_COMPONENT_NAME = 'LoaderCore';
    const CORE_COMPONENT_VERSION = '1.0.0';

    /** @var array */
    protected $filesMap = null;

    /** @var array */
    protected $aliases = null;

    /** @var LoaderCore  */
    static private $instance = null;

    public function __construct(){
        $stack = engine::option('stackObject');

        $this->aliases = new $stack();
        $this->filesMap = new $stack(engine::init()->getFilesMap());

        foreach(engine::init()->getAliases() as $alias => $array){
            foreach($array as $value){
                $this->registerAliasDirectory($alias, $value);
            }
        }
    }

    static public function loaderInit(){
        if(is_null(self::$instance)){
            $class = get_called_class();
            self::$instance = new $class;
        }
        return self::$instance;
    }

    public function load($file, $EXT = false) {
        return engine::app()->load($file, $EXT);
    }

    public function loadFilesFromMap($name) {
        return engine::app()->loadFilesFromMap($name);
    }

    public function registerAliasDirectory($aliases, $dir) {
        return engine::app()->registerAliasDirectory($aliases, $dir);
    }

    public function aliasDirectoryExist($alias){
        return engine::app()->aliasDirectoryExist($alias);
    }

    public function getRealPaths($path, $without_extension = false) {
        return engine::app()->getRealPaths($path, $without_extension);
    }

    static public function registerAlias($aliases, $dir) {
        engine::registerAlias($aliases, $dir);
    }

    static public function loadFile($file, $EXT = false) {
        return engine::loadFile($file, $EXT);
    }

    static public function loadPhar($file) {
        return engine::loadPhar($file);
    }

    static public function loadCore($name) {
        return engine::loadCore($name);
    }

    static public function loadMapFile($file) {
        return engine::loadMapFile($file);
    }

    static public function map($catalog, $index, $file) {
        return engine::map($catalog, $index, $file);
    }

    static public function loadMap($map, $autoload = false) {
        return engine::loadMap($map, $autoload);
    }
}

engine::registerComponent('loader', ['mfe\Loader\LoaderCore', 'loaderInit']);
