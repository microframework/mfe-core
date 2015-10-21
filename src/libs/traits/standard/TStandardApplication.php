<?php namespace mfe\core\libs\traits\standard;

use ArrayObject;
use Exception;
use InvalidArgumentException;
use mfe\core\api\applications\managers\IApplicationManager;
use mfe\core\api\components\managers\IComponentManager;
use mfe\core\applications\DefaultApplication;
use mfe\core\libs\components\CDebug;
use mfe\core\libs\components\CDisplay;
use mfe\core\libs\components\CException;
use mfe\core\Init;
use mfe\core\libs\http\CResponse;
use mfe\core\libs\managers\CApplicationManager;
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
    /** @var IApplicationManager */
    protected $applicationManager;

    /** @var IComponentManager */
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
     * @throws CException
     * @throws Exception
     */
    public function __TStandardApplication()
    {
        $this->applicationManager = new CApplicationManager();

        if ($defaultApplication = MfE::getConfigData('defaultApplication', false)) {
            $this->applicationManager->registerAsDefault(new DefaultApplication());
        }
        $this->response = new CResponse();

        $this->registerSystemPageStub();
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

    public function getApplicationManager()
    {
        return $this->applicationManager;
    }

    /**
     * @param string $applicationName
     *
     * @return ArrayObject|array
     */
    public function importInitConfig($applicationName)
    {
        // TODO::
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

        $application = MfE::getInstance()->applicationManager->application();
        if (!$application) {
            $application = MfE::getInstance();
        }

        return $application;
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
     * @param mixed|Object $value
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

    public function load()
    {
    }

    public function unload()
    {
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
        //$buffer->write((string)new SystemMfEPage());
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
