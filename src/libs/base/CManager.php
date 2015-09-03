<?php namespace mfe\core\libs\base;

use ArrayObject;

/**
 * Class CManager
 *
 * @package mfe\core\libs\base
 */
abstract class CManager extends CComponent
{
    /** @var ArrayObject */
    private $localRegister;

    /** @var ArrayObject|callable */
    private $globalRegister;

    public function __construct()
    {
        $this->localRegister = new ArrayObject();
    }

    /**
     * @return ArrayObject
     */
    public function getRegister()
    {
        if (null !== $this->globalRegister) {
            return $this->globalRegister;
        }
        return $this->localRegister;
    }

    /**
     * @param ArrayObject $register
     *
     * @return $this
     */
    public function setRegister(ArrayObject $register)
    {
        $registerClass = get_class($register);
        return $this->globalRegister = new $registerClass(array_merge((array)$register, (array)$this->localRegister));
    }

    public function flushRegister()
    {
        $this->localRegister = new ArrayObject();

        if (null !== $this->globalRegister) {
            $class = get_class($this->globalRegister);
            $config = (method_exists($this->globalRegister, 'getConfig'))
                ? call_user_func([$this->globalRegister, 'getConfig'])
                : null;

            $this->globalRegister = new $class;
            if (null !== $config) {
                call_user_func_array([$this->getRegister(), 'setConfig'], []);
            }
        }
    }
}
