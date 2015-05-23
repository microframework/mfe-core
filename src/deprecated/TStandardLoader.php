<?php namespace mfe\core\deprecated;

use mfe\core\libs\interfaces\ILoader;

use mfe\core\libs\components\CObjectsStack;
use mfe\core\libs\traits\application\TApplicationLoader;
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
 * @package mfe\core\libs\traits\standard
 */
trait TStandardLoader
{
    /** @var CObjectsStack */
    protected $aliases;

    /** @var CObjectsStack */
    protected $filesMap;

    /** @var array */
    protected $declareInit = [];

    use TApplicationLoader;

    /**
     * Behavior trait constructor
     */
    static public function TStandardLoader()
    {
        mfe::$register['TR'][] = 'aliases';
        mfe::$register['TR'][] = 'filesMap';
    }

    /**
     * Trait constructor
     */
    public function __TStandardLoader()
    {
        /** @var mfe $class */
        $class = static::class;
        $this->registerStandardLoader();

        // Register self as closing component
        $class::registerClosingComponent('loader', static::class);
    }

    /**
     * @param bool $undo
     * @return array
     */
    protected function registerStandardLoader($undo = false)
    {
        /** @var mfe $class */
        $class = static::class;

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
        $class = static::class;
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
        $class = static::class;
        /** @var mfe $class */
        $FileHelper = $class::option('FileHelper');
        $EXT = (!$EXT) ? $FileHelper::$PHP : $EXT;
        $paths = $this->getRealPaths($file);

        foreach ($paths as $file) {
            $file = str_replace('//', '/', $file);
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
}
