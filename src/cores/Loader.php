<?php namespace mfe\core\cores;

use mfe\core\libs\components\CException;
use mfe\core\deprecated\TStandardLoader;

use mfe\core\libs\base\CCore;

use mfe\core\mfe;

/**
 * Class Loader
 *
 * @deprecated
 * @package mfe\core\cores
 */
class Loader extends CCore
{
    use TStandardLoader;

    const COMPONENT_NAME = 'Loader';
    const COMPONENT_VERSION = '1.0.0';

    /** @var Loader */
    static public $instance;

    /**
     * Constructor
     *
     * @throws CException
     */
    public function __construct()
    {
        $stack = MfE::getConfigData('utility.StackObject');

        $this->aliases = new $stack();
        $this->filesMap = new $stack(MfE::app()->getFilesMap());

        foreach (MfE::app()->getAliases() as $alias => $array) {
            foreach ($array as $value) {
                $this->registerAliasDirectory($alias, $value);
            }
        }

        $this->registerLoader();
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
            (!$undo) ? MfE::app()->registerCoreComponent($key, $callback)
                : MfE::app()->unRegisterCoreComponent($key);
        }

        return $components;
    }
}
