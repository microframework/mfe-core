<?php namespace mfe\core\deprecated;

/**
 * Class TApplicationComponents
 *
 * @deprecated
 * @package mfe\core\libs\traits\application
 */
trait TApplicationComponents
{
    /**
     * Check component
     *
     * Whether checks there is a dependent component of system
     * Проверяет существует ли зависимый компонент системы
     *
     * @param string $componentName , Component name
     * @return bool
     */
    public function _hasComponent($componentName)
    {
        return (isset($this->components->components[$componentName])) ? true : false;
    }

    /**
     * Get component
     *
     * Tries to receive a dependent component of system if that exists
     * Пытается получить зависимый компонент системы, если тот существует
     *
     * @param string $componentName , Component name
     * @return callable|null
     */
    public function _getComponent($componentName)
    {
        return ($this->_hasComponent($componentName)) ? $this->components->components[$componentName] : null;
    }

    /**
     * Call component
     *
     * Tries to call a dependent component of system if that exists
     * Пытается вызвать зависимый компонент системы, если тот существует
     *
     * @param string $componentName , Component name
     * @param array $arguments
     * @return callable|null
     */
    public function _callComponent($componentName, $arguments = [])
    {
        return ($this->_hasComponent($componentName))
            ? call_user_func_array($this->components->components[$componentName], $arguments) : null;
    }

    /**
     * Check core component
     *
     * Whether checks there is a dependent component of a core
     * Проверяет существует ли зависимый компонент ядра
     *
     * @param string $componentName , Component name
     * @return bool
     */
    public function _hasCoreComponent($componentName)
    {
        return (isset($this->components->coreComponents[$componentName])) ? true : false;
    }

    /**
     * Get core component
     *
     * Tries to receive a dependent component of a core if that exists
     * Пытается получить зависимый компонент ядра, если тот существует
     *
     * @param string $componentName , Component name
     * @return callable|null
     */
    public function _getCoreComponent($componentName)
    {
        return ($this->_hasCoreComponent($componentName)) ? $this->components->coreComponents[$componentName] : null;
    }

    /**
     * Call core component
     *
     * Tries to call a dependent component of a core if that exists
     * Пытается вызвать зависимый компонент ядра, если тот существует
     *
     * @param string $componentName , Component name
     * @param array $arguments
     * @return callable|null
     */
    public function _callCoreComponent($componentName, $arguments = [])
    {
        if ($this->_hasCoreComponent($componentName)) {
            if (method_exists($this->components->coreComponents[$componentName][0], 'getInstance')) {
                return call_user_func_array([
                    call_user_func_array([$this->components->coreComponents[$componentName][0], 'getInstance'], []),
                    $this->components->coreComponents[$componentName][1]
                ], $arguments);
            } else {
                return call_user_func_array($this->components->coreComponents[$componentName], $arguments);
            }
        }
        return null;
    }

    /**
     * Check closing component
     *
     * Whether checks there is a dependent closing component
     * Проверяет существует ли замыкаемый компонент ядра
     *
     * @param string $componentName , Component name
     * @return bool
     */
    public function _hasClosingComponent($componentName)
    {
        return (isset($this->closingComponents[$componentName])) ? true : false;
    }

    /**
     * Get closing component
     *
     * Tries to receive a closing component if that exists
     * Пытается получить замыкаемый компонент, если тот существует
     *
     * @param string $componentName , Component name
     * @return callable|null
     */
    public function _getClosingComponent($componentName)
    {
        return ($this->_hasClosingComponent($componentName)) ? $this->closingComponents[$componentName] : null;
    }

    /**
     * Call closing component
     *
     * Tries to call a closing component if that exists
     * Пытается вызвать замыкаемый компонент, если тот существует
     *
     * @param string $componentName , Component name
     * @param array $arguments
     * @return callable|null
     */
    public function _callClosingComponent($componentName, $arguments = [])
    {
        return ($this->_hasClosingComponent($componentName)) ?
            call_user_func_array($this->closingComponents[$componentName], $arguments) : null;
    }

    /**
     * Add component to system
     *
     * @param $name
     * @param $callback
     * @return bool|callable
     */
    public function _registerComponent($name, $callback)
    {
        return (is_callable($callback)) ? $this->components->components[$name] = $callback : false;
    }


    /**
     * Add core component to system
     *
     * @param $name
     * @param $callback
     * @return bool|callable
     */
    public function _registerCoreComponent($name, $callback)
    {
        return (is_callable($callback)) ? $this->components->coreComponents[$name] = $callback : false;
    }

    /**
     * Add closing component to system
     *
     * @param $name
     * @param $callback
     * @return bool|callable
     */
    public function _registerClosingComponent($name, $callback)
    {
        return (is_string($callback) || is_object($callback)) ? $this->closingComponents[$name] = $callback : false;
    }

    /**
     * Delete component from system
     *
     * @param $name
     * @return null
     */
    public function _unRegisterComponent($name)
    {
        return ($this->_hasComponent($name)) ? $this->components->components[$name] = null : null;
    }

    /**
     * Delete core component from system
     *
     * @param $name
     * @return null
     */
    public function _unRegisterCoreComponent($name)
    {
        return ($this->_hasCoreComponent($name)) ? $this->components->coreComponents[$name] = null : null;
    }

    /**
     * Delete closing component from system
     *
     * @param $name
     * @return null
     */
    public function _unRegisterClosingComponent($name)
    {
        return ($this->_hasClosingComponent($name)) ? $this->closingComponents[$name] = null : null;
    }
}
