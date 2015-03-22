<?php namespace mfe\core\core;

use mfe\core\libs\interfaces\IComponent;
use mfe\core\libs\traits\standard\TStandardLoader;

use mfe\core\libs\components\CCore;

use mfe\core\mfe;

/**
 * Class Loader
 * @package mfe\core\core
 */
class Loader extends CCore implements IComponent
{
    use TStandardLoader;

    const COMPONENT_NAME = 'Loader';
    const COMPONENT_VERSION = '1.0.0';

    /** @var Loader */
    static public $instance;

    /**
     * Constructor
     */
    public function __construct()
    {
        $stack = mfe::option('stackObject');

        $this->aliases = new $stack();
        $this->filesMap = new $stack(mfe::app()->getFilesMap());

        foreach (mfe::app()->getAliases() as $alias => $array) {
            foreach ($array as $value) {
                $this->registerAliasDirectory($alias, $value);
            }
        }

        $this->registerLoader();
        return self::$instance;
    }

    /**
     * @param bool $undo
     * @return array
     */
    protected function registerLoader($undo = false)
    {
        $components = [
            'registerAlias' => [__CLASS__, '_registerAlias'],
            'loadFile' => [__CLASS__, '_loadFile'],
            'loadPhar' => [__CLASS__, '_loadPhar'],
            'loadCore' => [__CLASS__, '_loadCore'],
            'loadMapFile' => [__CLASS__, '_loadMapFile'],
            'map' => [__CLASS__, '_map'],
            'loadMap' => [__CLASS__, '_loadMap'],
        ];

        foreach ($components as $key => $callback) {
            (!$undo) ? mfe::app()->registerCoreComponent($key, $callback)
                : mfe::app()->unRegisterCoreComponent($key);
        }

        return $components;
    }

    /**
     * @return bool
     */
    static public function registerComponent()
    {
        mfe::registerComponent('loader', [static::class, 'getInstance']);
        return true;
    }
}
