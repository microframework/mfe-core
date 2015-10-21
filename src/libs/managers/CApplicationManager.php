<?php namespace mfe\core\libs\managers;

use ArrayObject;
use mfe\core\api\applications\IApplication;
use mfe\core\api\applications\managers\IApplicationManager;
use mfe\core\api\stack\IObjectsStack;
use mfe\core\libs\base\CManager;
use mfe\core\libs\components\CException;
use mfe\core\libs\components\CObjectsStack as Stack;

/**
 * Class CApplicationManager
 *
 * @package mfe\core\libs\managers
 * @version 1.0.0
 */
class CApplicationManager extends CManager implements IApplicationManager
{
    const COMPONENT_NAME = 'ApplicationManager';
    const COMPONENT_VERSION = '1.0.0';

    /** @var ArrayObject|Stack|IApplication[] */
    protected $applicationStack;

    /** @var string */
    protected $defaultApplication;

    /** @var string */
    protected $currentApplication;

    /**
     * @param IApplication|null $application
     *
     * @throws CException
     */
    public function __construct(IApplication $application = null)
    {
        parent::__construct();
        $this->applicationStack = new Stack();

        // Load default application if exist
        if (null !== $application) {
            $this->registerApplication($application);
            $this->loadApplication($application);

            $this->currentApplication = get_class($application);
        }
    }

    /**
     * @return IApplication|null
     */
    public function application()
    {
        if ($this->currentApplication) {
            return $this->applicationStack[$this->currentApplication];
        }
        return null;
    }

    /**
     * @param IApplication $application
     *
     * @return static
     * @throws CException
     */
    public function registerApplication(IApplication $application)
    {
        $this->applicationStack->add(get_class($application), $application);
        return $this;
    }

    /**
     * @param IApplication|string $application
     *
     * @return static
     */
    public function removeApplication($application)
    {
        if ($application instanceof IApplication) {
            $application = get_class($application);
        }

        $this->applicationStack->remove($application);
        return $this;
    }

    /**
     * @param IApplication $application
     *
     * @return static
     * @throws CException
     */
    public function loadApplication(IApplication $application)
    {
        $applicationName = get_class($application);

        if (!$this->applicationStack->has($applicationName)) {
            throw new CException('Load unregistered application:' . $applicationName);
        }

        if (null !== $this->currentApplication) {
            $oldApplication = $this->applicationStack[$this->currentApplication];
            $oldApplication->unload();
        }

        $this->currentApplication = $applicationName;
        $application->load();
        return $this;
    }

    /**
     * @param IApplication $application
     * @param bool $loadDefaultApplication
     *
     * @return static
     * @throws CException
     */
    public function unloadApplication(IApplication $application, $loadDefaultApplication = true)
    {
        $applicationName = get_class($application);

        if (!$this->applicationStack->has($applicationName)) {
            throw new CException('Unload unregistered application:' . $applicationName);
        }

        $this->currentApplication = null;
        $application->unload();

        // Load default application if exist
        if ($loadDefaultApplication && null !== $this->defaultApplication
            && $application = $this->applicationStack[$this->defaultApplication]
        ) {
            $this->currentApplication = $this->defaultApplication;
            $application->load();
        }
        return $this;
    }

    /* +@Override Section  */

    /**
     * @Override
     *
     * @return ArrayObject
     */
    public function getRegister()
    {
        return $this->applicationStack;
    }

    /**
     * @Override
     *
     * @param ArrayObject $applicationStack
     *
     * @return static
     * @throws CException
     */
    public function setRegister(ArrayObject $applicationStack)
    {
        if (!($applicationStack instanceof IObjectsStack)) {
            throw new CException('applicationStack must be instance of ' . IObjectsStack::class);
        }
        $this->applicationStack = $applicationStack;
        return $this;
    }

    /**
     * @Override
     *
     * @return static
     */
    public function flushRegister()
    {
        $this->applicationStack = new Stack();
        return $this;
    }

    /* -@Override Section  */
} 
