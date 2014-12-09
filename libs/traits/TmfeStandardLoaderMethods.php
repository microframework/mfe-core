<?php namespace mfe;

/**
 * Class TmfeStandardLoaderMethods
 *
 * @property mixed components
 * @property ImfeLoader loader
 *
 * @package mfe
 */
trait TmfeStandardLoaderMethods {
    /** @var CmfeObjectsStack */
    protected $aliases = null;

    /** @var CmfeObjectsStack */
    protected $filesMap = null;

    static public function TmfeStandardLoaderMethodsInit() {
        mfe::$register[] = 'aliases';
        mfe::$register[] = 'filesMap';
    }

    public function getFilesMap(){
        return (array)$this->filesMap;
    }

    public function getAliases(){
        return (array)$this->aliases;
    }

    public function registerAliasDirectory($aliases, $dir) {
        if (isset($this->components->components['loader'])) {
            return $this->loader->registerAliasDirectory($aliases, $dir);
        }
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

    protected function getRealPaths($path) {
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
        if (isset($extension)) $result['extension'] = $extension;
        return $result;
    }

    public function load($file, $PHAR = false) {
        if (isset($this->components->components['loader'])) {
            return $this->loader->load($file, $PHAR);
        } else {
            $class = get_called_class();
            /** @var mfe $class */
            $FileHelper = $class::option('FileHelper');
            $EXT = (!$PHAR) ? $FileHelper::$PHP : $FileHelper::$Phar;
            $paths = $this->getRealPaths($file);
            if (isset($paths['extension'])) {
                $extension = $paths['extension'];
                unset($paths['extension']);
            } else $extension = '';
            foreach ($paths as $file) {
                #print $file . '.' . $extension . $EXT . PHP_EOL;
                if (file_exists($file . '.' . $extension . $EXT)) {
                    $class::trigger('file.load', [$file . '.' . $extension . $EXT]);
                    /** @noinspection PhpIncludeInspection */
                    return include_once $file . '.' . $extension . $EXT;
                }
            }
            return false;
        }
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


    static public function registerAlias($aliases, $dir) {
        $class = get_called_class();
        /** @var mfe $class */
        $class::trigger('alias.register', [$aliases, $dir]);
        $class::init()->registerAliasDirectory($aliases, $dir);
    }

    static public function loadFile($file, $PHAR = false) {
        $class = get_called_class();
        /** @var mfe $class */
        if (isset($class::init()->loader)) {
            return call_user_func_array([get_class($class::init()->loader), __METHOD__], [$file, $PHAR]);
        }
        return $class::init()->load($file, $PHAR);
    }

    static public function loadPhar($file) {
        $class = get_called_class();
        /** @var mfe $class */
        $class::trigger('phar.load', [$file]);
        if (isset($class::init()->loader)) {
            return call_user_func_array([get_class($class::init()->loader), __METHOD__], [$file]);
        }
        return self::loadFile($file, TRUE);
    }

    static public function loadCore($name) {
        $class = get_called_class();
        /** @var mfe $class */
        $class::trigger('file.loadCore', [$name]);
        if (isset($class::init()->loader)) {
            return call_user_func_array([get_class($class::init()->loader), __METHOD__], [$name]);
        }
        return self::loadFile('@engine.@core.' . $name . '.core');
    }

    static public function loadMapFile($file) {
        $class = get_called_class();
        /** @var mfe $class */
        $class::trigger('file.loadMap', [$file]);
        if (isset($class::init()->loader)) {
            return call_user_func_array([get_class($class::init()->loader), __METHOD__], [$file]);
        }
        return self::loadFile('@engine.' . $file . '.map');
    }

    static public function map($catalog, $index, $file) {
        $class = get_called_class();
        /** @var mfe $class */
        if (isset($class::init()->loader)) {
            return call_user_func_array([get_class($class::init()->loader), __METHOD__], [$file]);
        }
        return $class::init()->filesMap[$catalog][$index] = $file;
    }

    static public function loadMap($map, $autoload = false) {
        $class = get_called_class();
        /** @var mfe $class */
        $class::trigger('map.load', [$map, $autoload]);
        if (isset($class::init()->loader)) {
            return call_user_func_array([get_class($class::init()->loader), __METHOD__], [$map]);
        }
        if (is_string($map)) {
            return $class::init()->loadFilesFromMap($map);
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
}
