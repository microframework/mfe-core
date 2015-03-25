<?php namespace mfe\core\libs\base;

use ArrayObject;

/**
 * Class CManager
 * @package mfe\core\libs\base
 */
abstract class CManager extends CComponent
{
    /** @var ArrayObject */
    private $localRegister;

    /** @var ArrayObject */
    private $globalRegister;

    /** @var CManager */
    static public $instance;

    public function __construct()
    {
        $this->localRegister = new ArrayObject();
    }

    /**
     * @return ArrayObject
     */
    protected function getRegister()
    {
        if (null !== $this->globalRegister) {
            return $this->globalRegister;
        }
        return $this->localRegister;
    }

    /**
     * @param ArrayObject $register
     * @return $this
     */
    public function setRegister(ArrayObject $register)
    {
        $registerClass = get_class($register);
        return $this->globalRegister = new $registerClass(array_merge((array)$register, (array)$this->localRegister));
    }
}
