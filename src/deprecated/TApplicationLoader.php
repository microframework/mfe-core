<?php namespace mfe\core\deprecated;

use mfe\core\mfe;

/**
 * Class TApplicationLoader
 * @package mfe\core\libs\traits\application
 */
trait TApplicationLoader
{
    /**
     * @param $aliases
     * @param $dir
     */
    public function _registerAlias($aliases, $dir)
    {
        /** @var mfe $class */
        $class = static::class;

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
        $class = static::class;

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
        $class = static::class;

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
        $class = static::class;

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
        $class = static::class;

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
