<?php
/**
 * Created by PhpStorm.
 * User: Димитрий
 * Date: 03.09.2015
 * Time: 0:52
 */
namespace mfe\core\libs\managers;
use mfe\core\libs\components\CException;
use ArrayObject;
use mfe\core\libs\system\SystemException;


/**
 * Class CComponentManager
 *
 * @pattern ServiceLocator
 * @package mfe\core\libs\managers
 */
interface IComponentManager
{
    /**
     * @param array $config
     *
     * @return $this
     */
    public function initComponentManager(array $config);

    /**
     *
     */
    public function flushComponentManager();

    /**
     * @param bool $returnDefinitions
     *
     * @return array|ArrayObject
     */
    public function getComponents($returnDefinitions = true);

    /**
     * @param $components
     *
     * @return $this
     * @throws CException
     */
    public function setComponents($components);

    /**
     * @param string $key
     *
     * @return null
     * @throws SystemException
     */
    public function get($key);

    /**
     * @param string $key
     * @param bool $checkInstance
     *
     * @return bool
     */
    public function has($key, $checkInstance = false);

    /**
     * @param string $key
     * @param object|callable $definition
     *
     * @return bool
     * @throws CException
     */
    public function set($key, $definition);

    /**
     * @param string $key
     * @param array $arguments
     *
     * @return mixed
     * @throws SystemException
     */
    public function call($key, array $arguments);

    /**
     * @param $definition
     *
     * @return bool|mixed|null
     * @throws SystemException
     */
    public function buildComponent($definition);
}
