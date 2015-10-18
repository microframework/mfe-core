<?php namespace mfe\core\libs\traits\standard;

use ArrayObject;
use Exception;
use InvalidArgumentException;
use mfe\core\libs\applications\CApplication;
use mfe\core\libs\components\CDebug;
use mfe\core\libs\components\CDisplay;
use mfe\core\libs\components\CException;
use mfe\core\libs\components\CObjectsStack;
use mfe\core\Init;
use mfe\core\libs\http\CResponse;
use mfe\core\libs\managers\CComponentManager;
use mfe\core\libs\system\page\SystemMfEPage;
use mfe\core\libs\system\Stream;
use mfe\core\libs\system\SystemException;
use mfe\core\mfe;
use Psr\Http\Message\ResponseInterface;

/**
 * Class TStandardApplication
 *
 * @package mfe\core\libs\traits\standard
 */
trait TStandardApplication
{
    /** @var CObjectsStack */
    protected $applications;

    public $currentApplication;

    /** @var CComponentManager */ //TODO:: to interface
    protected $componentManager;

    private $container = [];

    /** @var ResponseInterface */
    public $response;

    static protected $config = [];
    static public $_STATUS = 0x0000000;

    /**
     * Behavior trait constructor
     */
    static public function TStandardApplication()
    {
        MfE::$traitsRegister[] = 'applications';
    }

    /**
     * Trait Constructor
     *
     * @throws InvalidArgumentException
     */
    public function __TStandardApplication()
    {
        $this->response = new CResponse();
        $this->registerSystemPageStub();
    }

    /**
     * @param CApplication $application
     * @param bool $setAsCurrentApplication
     *
     * @return bool
     * @throws Exception
     */
    public function registerApplication(CApplication $application, $setAsCurrentApplication = true)
    {
        if ($setAsCurrentApplication) {
            $this->currentApplication = get_class($application);
        }
        MfE::getInstance()->applications->add(get_class($application), $application);
        return true;
    }

    /**
     * @return bool
     * @throws CException
     */
    public function registerComponentManager()
    {
        /** @var array $componentManager */
        if ($componentManager = MfE::getConfigData('components.di')) {
            if (!class_exists($componentManager['class'])) {
                throw new CException("Defined components.di~>[class]|{$componentManager['class']} in config is not exists!");
            }
            $this->container = new ArrayObject($this->container);

            $this->componentManager = new $componentManager['class'];
            $this->componentManager
                ->initComponentManager($componentManager)
                ->setComponents(MfE::getConfigData('components'))
                ->setRegister($this->container);
        } else {
            throw new CException('Not defined components.di~>[class] in configs file');
        }
        return true;
    }

    /**
     * @param $componentManager
     *
     * @return bool
     * @throws CException
     */
    public function importComponentManager(&$componentManager)
    {
        if (!$this->componentManager) {
            $this->registerComponentManager();
        }

        $componentManager = $this->componentManager;
        return true;
    }

    /**
     * @param string $applicationName
     *
     * @return ArrayObject|array
     */
    public function importInitConfig($applicationName)
    {
        return new ArrayObject();
    }

    /**
     * TODO:: Application stack
     *
     * @param Init $config
     *
     * @return mfe
     * @throws CException
     */
    static public function app(Init $config = null)
    {
        if (null !== $config && is_callable($config)) {
            static::$config = $config();
        }

        if (!count(MfE::getInstance()->applications)) {
            $application = MfE::getInstance();
        } else {
            $application = MfE::getInstance()->applications->{MfE::getInstance()->currentApplication};
        }

        return $application;
    }

    /**
     * TODO:: Refactor this to AppStack
     *
     * @param string $name
     *
     * @return static $this
     * @throws CException
     */
    public function loadApplication($name)
    {
        if ($this instanceof $name) {
            return $this;
        }
        if (class_exists($name) && !MfE::getInstance()->currentApplication) {
            MfE::getInstance()->currentApplication = $name;
            new $name;
            return MfE::getInstance()->applications->{MfE::getInstance()->currentApplication};
        }

        throw new CException('Unknown application: ' . $name);
    }

    /**
     * @param $error_code
     *
     * @return bool|null
     */
    static public function stop($error_code)
    {
        self::$_STATUS = $error_code;
        return CDebug::criticalStopEngine($error_code);
    }

    /**
     * @param $data
     * @param $type
     */
    static public function display($data, $type)
    {
        CDisplay::display($data, $type);
    }

    /**
     * @param string $key
     *
     * @return mixed|null:
     * @throws SystemException
     * @throws CException
     */
    public function get($key)
    {
        if (!$this->componentManager) {
            throw new CException('ComponentManager not initialized!');
        }
        return array_key_exists($key, $this->container) ? $this->container[$key] : $this->componentManager->get($key);
    }

    /**
     * @param string $key
     * @param object $value
     *
     * @return $this
     * @throws CException
     */
    public function set($key, $value)
    {
        if (!$this->componentManager) {
            throw new CException('ComponentManager not initialized!');
        }
        $this->componentManager->set($key, $value);
        return $this;
    }

    /**
     * @param string $key
     *
     * @return bool
     * @throws CException
     */
    public function has($key)
    {
        if (!$this->componentManager) {
            throw new CException('ComponentManager not initialized!');
        }
        return $this->componentManager->has($key);
    }

    /**
     * @param string $key
     * @param array $arguments
     *
     * @return mixed
     * @throws SystemException
     * @throws CException
     */
    public function call($key, array $arguments = [])
    {
        if (!$this->componentManager) {
            throw new CException('ComponentManager not initialized!');
        }
        $this->componentManager->call($key, $arguments);
    }


    /*** @Заглушки ***/

    /**
     * Components getter handler
     *
     * @param $key
     *
     * @return mixed|null
     * @throws SystemException
     * @throws CException
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    protected function registerSystemPageStub()
    {
        $buffer = new Stream('php://memory', 'w+');
        $buffer->write((string)new SystemMfEPage());
        $this->response = $this->response->withBody($buffer);
    }

    /*
        public function __invoke($arguments)
        {

        }
        final public function __debugInfo() {
            return [MFE_VERSION];
        }

        final static public function __set_state($array) {
            //return [MFE_VERSION];
        }

        final public function __clone()
        {
            throw new CException('mfe can\'t be cloned');
        }

        final public function __sleep()
        {
            throw new CException('mfe can\'t be serialized');
        }

        final public function __wakeup()
        {
            throw new CException('mfe can\'t be serialized');
        }
    */
}
