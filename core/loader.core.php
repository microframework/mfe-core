<?php namespace mfe\Loader;

use mfe\mfe as engine;
use mfe\ImfeComponent;

class LoaderCore implements ImfeComponent {
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
        $FileHelper = engine::option('FileHelper');
        $EXT = (!$EXT) ? $FileHelper::$PHP : $EXT;
        $paths = $this->getRealPaths($file);

        foreach ($paths as $file) {
            #print $file . $EXT . PHP_EOL;
            if (file_exists($file . $EXT)) {
                engine::trigger('file.load', [$file . $EXT]);
                /** @noinspection PhpIncludeInspection */
                return ($EXT == $FileHelper::$PHP || $EXT == $FileHelper::$Phar) ?
                    include_once $file . $EXT : file_get_contents($file . $EXT);
            }
        }
        return false;
    }

    public function loadFilesFromMap($name) {
        if (isset($this->filesMap[$name])
            && is_array($this->filesMap[$name])
            && !empty($this->filesMap[$name])
        ) {
            foreach ($this->filesMap[$name] as $file) {
                $this->load($file);
            }
        }
        return $this;
    }

    public function registerAliasDirectory($aliases, $dir) {
        if (is_array($aliases)) {
            foreach ($aliases as $alias) {
                if ((string)$alias[0] === '@') {
                    $this->aliases[strtolower($alias)][] = str_replace(['\\', '//'], '/', $dir);
                }
            }
        } else {
            if ((string)$aliases[0] === '@') {
                $this->aliases[strtolower($aliases)][] = str_replace(['\\', '//'], '/', $dir);
            }
        }
        return $this;
    }

    public function aliasDirectoryExist($alias){
        if(isset($this->aliases[strtolower($alias)]) && count($this->aliases[strtolower($alias)]) >= 1)
            return (array) $this->aliases[strtolower($alias)];
        return false;
    }

    public function getRealPaths($path, $without_extension = false) {
        $FileHelper = engine::option('FileHelper');
        $result = [];
        $path_nodes = explode('.', $path);
        if (!empty($path_nodes) && count($path_nodes) >= 2) {
            $extension = array_reverse($path_nodes)[0];
            unset($path_nodes[count($path_nodes) - 1]);
            foreach ($path_nodes as $path) {
                if (isset($this->aliases[$path])) {
                    $nodes = $result;
                    $result = [];
                    foreach (array_reverse($this->aliases[$path]) as $part) {
                        if (!empty($nodes)) {
                            foreach ($nodes as $node) {
                                if ($node !== $part) $result[] = $node . $FileHelper::$SEPARATOR . $part;
                            }
                        } else $result[] = $part;
                    }
                } else {
                    $nodes = $result;
                    $result = [];
                    if (!empty($nodes)) {
                        foreach ($nodes as $node) {
                            $result[] = $node . $FileHelper::$SEPARATOR . $path;
                        }
                    } else $result[] = $path;
                }
            }
        } else $result[] = $path;

        if (isset($extension) && !$without_extension) {
            $temp = [];
            foreach($result as $path){
                $temp[] = $path . '.' . $extension;
                $temp[] = $path . '/' . $extension;
            }
            $result = $temp;
        };
        return $result;
    }

    static public function registerAlias($aliases, $dir) {
        engine::trigger('alias.register', [$aliases, $dir]);
        self::$instance->registerAliasDirectory($aliases, $dir);
    }

    static public function loadFile($file, $EXT = false) {
        return self::$instance->load($file, $EXT);
    }

    static public function loadPhar($file) {
        engine::trigger('phar.load', [$file]);
        return self::loadFile($file, TRUE);
    }

    static public function loadCore($name) {
        engine::trigger('file.loadCore', [$name]);
        return self::loadFile('@engine.@core.' . $name . '.' . $name);
    }

    static public function loadMapFile($file) {
        engine::trigger('file.loadMap', [$file]);
        return self::loadFile('@engine.' . $file . '.map');
    }

    static public function map($catalog, $index, $file) {
        return self::$instance->filesMap[$catalog][$index] = $file;
    }

    static public function loadMap($map, $autoload = false) {
        engine::trigger('map.load', [$map, $autoload]);
        if (is_string($map)) {
            return self::$instance->loadFilesFromMap($map);
        } elseif (is_array($map)) {
            foreach ($map as $list) {
                if (count($list) >= 3)
                    self::map($list[0], $list[1], $list[2]);
            }
            if ($autoload && is_string($autoload)) {
                self::loadMap($autoload);
            }
            if ($autoload && is_array($autoload)) {
                foreach ($autoload as $list) {
                    if (is_string($list)) self::loadMap($list);
                }
            }
        }
        return true;
    }

    static public function registerComponent() {
        return self::loaderInit();
    }
}

engine::registerComponent('loader', 'mfe\Loader\LoaderCore');
