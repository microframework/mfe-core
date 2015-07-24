<?php namespace mfe\core\libs\system;

use ArrayObject;

/*
 * TODO:: make by alias
 * TODO:: cascade magic in factory constructor
 * TODO:: loading class|interface map method
 * TODO:: Closure as constructor
 * TODO:: Closure as constructor rebind in ServiceLocator|ComponentManager
 */

/**
 * Class IoC (Inversion of Control)
 *
 * @property array|ArrayObject $container
 *
 * @package mfe\core\libs\system
 */
class IoC extends Object
{
    const TYPE_INSTANCE = 'Instance';
    const TYPE_SINGLETON = 'Singleton';

    /** @var string */
    private $currentObjectAlias;

    /** @var string */
    private $currentType;

    /** @var array|ArrayObject */
    private $alias = [];

    /** @var array|ArrayObject */
    private $instances = [];

    /** @var array|ArrayObject */
    private $singletons = [];

    public function __construct()
    {
        $this->alias = new ArrayObject();
        $this->instances = new ArrayObject();
        $this->singletons = new ArrayObject();
        $this->setContainer(new ArrayObject());
    }

    /**
     * @param string $name
     * @param string|object|callable $callable
     *
     * @return IoC
     */
    public function bind($name, $callable)
    {
        $this->alias->$name = $callable;
        return $this;
    }

    /**
     * @param array $definition
     * @param bool $overwrite
     *
     * @return $this
     * @throws SystemException
     */
    public function instance(array $definition, $overwrite = false)
    {
        $this->setCurrentObject($definition['class'], IoC::TYPE_INSTANCE);

        if (!$overwrite && array_key_exists($definition['class'], $this->instances)) {
            throw new SystemException('Not allowed to overwrite this instance.');
        }

        $this->instances[$definition['class']] = array_merge($definition, [
            'class' => $definition['class'],
            'type' => IoC::TYPE_INSTANCE
        ]);
        return $this;
    }

    /**
     * @param array $definition
     * @param bool $overwrite
     *
     * @return IoC
     * @throws SystemException
     */
    public function singleton(array $definition, $overwrite = false)
    {
        $this->setCurrentObject($definition['class'], IoC::TYPE_SINGLETON);

        if (!$overwrite && array_key_exists($definition['class'], $this->singletons)) {
            throw new SystemException('Not allowed to overwrite this singleton.');
        }

        $this->singletons[$definition['class']] = array_merge($definition, [
            'class' => $definition['class'],
            'type' => IoC::TYPE_SINGLETON
        ]);
        return $this;
    }

    /**
     * @param $class
     * @param $type
     */
    protected function setCurrentObject($class, $type)
    {
        $this->currentObjectAlias = $class;
        $this->currentType = $type;
    }

    /**
     * @param $define
     *
     * @return object
     */
    public function build($define)
    {
        $object1 = new $define['class'];

        foreach ($define as $key => $value) {
            if ('class' !== $key && 'type' !== $key) {
                $object1->$key = $value;
            }
        }

        return $object1;
    }

    /**
     * @param $name
     * @return array|bool
     */
    public function find($name)
    {
        if (array_key_exists($name, $this->singletons)) {
            return [$this->singletons[$name]['class'], IoC::TYPE_SINGLETON];
        } elseif (array_key_exists($name, $this->instances)) {
            return [$this->instances[$name]['class'], IoC::TYPE_INSTANCE];
        }
        return false;
    }

    /**
     * @param $name
     * @return bool
     */
    public function isSingleton($name)
    {
        return IoC::TYPE_SINGLETON === $this->find($name)[1];
    }

    /**
     * @param string|null $name
     *
     * @return mixed|null
     * @throws SystemException
     */
    public function make($name = null)
    {
        if (!$this->currentObjectAlias && !$name) {
            throw new SystemException('You try make not defined object.');
        } elseif ($name && $resolved = $this->find($name)) {
            list($this->currentObjectAlias, $this->currentType) = $resolved;
        }

        $name = $name ?: $this->currentObjectAlias;

        if (IoC::TYPE_SINGLETON === $this->currentType) {

            if (!$this->has($name)) {
                $this->set($name, $this->build($this->singletons[$name]));
            }
            return $this->get($name);
        }

        return $this->build($this->instances[$name]);
    }
}
