<?php namespace mfe\core\libs\traits;

use mfe\core\libs\interfaces\ILoader;

use mfe\core\libs\components\CObjectsStack;
use mfe\core\mfe;

//TODO:: Добавить документацию ко всем методам

/**
 * Class TmfeStandardLoader
 *
 * @property mixed components
 * @property ILoader loader
 *
 * @method static void registerAlias
 * @method static bool|mixed|string loadFile
 * @method static bool|mixed|string loadPhar
 * @method static bool|mixed|string loadCore
 * @method static bool|mixed|string loadMapFile
 * @method static mixed map
 * @method static bool loadMap
 *
 * @package mfe
 */
trait TStandardLoader
{
    /** @var CObjectsStack */
    protected $aliases = null;

    /** @var CObjectsStack */
    protected $filesMap = null;

    /** @var array */
    protected $declareInit = [];

    /**
     * Behavior trait constructor
     */
    static public function TStandardLoader()
    {
        mfe::$register[] = 'aliases';
        mfe::$register[] = 'filesMap';
    }

    /**
     * Trait constructor
     */
    public function __TStandardLoader()
    {
        /** @var mfe $class */
        $class = get_called_class();
        $this->registerStandardLoader();

        // Register self as closing component
        $class::registerClosingComponent('loader', get_called_class());
    }

    /**
     * @param bool $undo
     * @return array
     */
    protected function registerStandardLoader($undo = false)
    {
        /** @var mfe $class */
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
            (!$undo) ? $class::registerCoreComponent($key, $callback)
                : $class::unRegisterCoreComponent($key);
        }

        return $components;
    }

    /**
     * @return array
     */
    public function getFilesMap()
    {
        return (array)$this->filesMap;
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return (array)$this->aliases;
    }

    /**
     * @param $aliases
     * @param $dir
     * @return $this
     */
    public function registerAliasDirectory($aliases, $dir)
    {
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

    /**
     * @param $alias
     * @return array|bool
     */
    public function aliasDirectoryExist($alias)
    {
        if (isset($this->aliases[strtolower($alias)]) && count($this->aliases[strtolower($alias)]) >= 1)
            return (array)$this->aliases[strtolower($alias)];
        return false;
    }

    /**
     * @param $path
     * @param bool $without_extension
     * @return array
     */
    public function getRealPaths($path, $without_extension = false)
    {
        $class = get_called_class();
        /** @var mfe $class */
        $FileHelper = $class::option('FileHelper');
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
            foreach ($result as $path) {
                $temp[] = $path . '.' . $extension;
                $temp[] = $path . '/' . $extension;
            }
            $result = $temp;
        };
        return $result;
    }

    /**
     * @param $file
     * @param bool $EXT
     * @return bool|mixed|string
     */
    public function load($file, $EXT = false)
    {
        $class = get_called_class();
        /** @var mfe $class */
        $FileHelper = $class::option('FileHelper');
        $EXT = (!$EXT) ? $FileHelper::$PHP : $EXT;
        $paths = $this->getRealPaths($file);

        foreach ($paths as $file) {
            #print $file . $EXT . PHP_EOL;
            if (file_exists($file . $EXT)) {
                mfe::trigger('file.load', [$file . $EXT]);
                /** @noinspection PhpIncludeInspection */
                return ($EXT == $FileHelper::$PHP || $EXT == $FileHelper::$Phar) ?
                    require_once $file . $EXT : file_get_contents($file . $EXT);
            }
        }
        return false;
    }

    /**
     * @param $name
     * @return $this
     */
    public function loadFilesFromMap($name)
    {
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

    /**
     * @param $aliases
     * @param $dir
     */
    public function _registerAlias($aliases, $dir)
    {
        /** @var mfe $class */
        $class = get_called_class();

        $class::trigger('alias.register', [$aliases, $dir]);

        $this->registerAliasDirectory($aliases, $dir);
    }

    /**
     * @param $file
     * @param bool $EXT
     * @return bool|mixed|string
     */
    public function _loadFile($file, $EXT = false)
    {
        return $this->load($file, $EXT);
    }

    /**
     * @param $file
     * @return bool|mixed|string
     */
    public function _loadPhar($file)
    {
        /** @var mfe $class */
        $class = get_called_class();

        $class::trigger('phar.load', [$file]);

        return $this->_loadFile($file, TRUE);
    }

    /**
     * @param $name
     * @return bool|mixed|string
     */
    public function _loadCore($name)
    {
        /** @var mfe $class */
        $class = get_called_class();

        $class::trigger('file.loadCore', [$name]);
        if ($core = $this->_loadFile('@engine.@core.' . $name . '.core')) {
            $classes = get_declared_classes();
            $init = end($classes);
            if (!isset($this->declareInit[$init]) && in_array('mfe\core\libs\interfaces\IComponent', class_implements($init))) {
                if (method_exists($init, 'registerComponent')) {
                    $reflection = new \ReflectionMethod($init, 'registerComponent');
                    if (!$reflection->isAbstract() && $reflection->isStatic() && $reflection->isPublic()) {
                        $this->declareInit[$init] = (bool)$init::registerComponent();
                    }
                }
            }
            return $core;
        }
        return false;
    }

    /**
     * @param $file
     * @return bool|mixed|string
     */
    public function _loadMapFile($file)
    {
        /** @var mfe $class */
        $class = get_called_class();

        $class::trigger('file.loadMap', [$file]);

        return $this->_loadFile('@engine.' . $file . '.map');
    }

    /**
     * @param $catalog
     * @param $index
     * @param $file
     * @return mixed
     */
    public function _map($catalog, $index, $file)
    {
        return $this->filesMap[$catalog][$index] = $file;
    }

    /**
     * @param $map
     * @param bool $autoload
     * @return bool
     */
    public function _loadMap($map, $autoload = false)
    {
        /** @var mfe $class */
        $class = get_called_class();

        $class::trigger('map.load', [$map, $autoload]);

        if (is_string($map)) {
            return $this->loadFilesFromMap($map);
        } elseif (is_array($map)) {
            foreach ($map as $list) {
                if (count($list) >= 3)
                    $this->_map($list[0], $list[1], $list[2]);
            }
            if ($autoload && is_string($autoload)) {
                $this->_loadMap($autoload);
            }
            if ($autoload && is_array($autoload)) {
                foreach ($autoload as $list) {
                    if (is_string($list)) $this->_loadMap($list);
                }
            }
        }
        return true;
    }
}
