<?php namespace mfe\core\api\applications\managers;

use mfe\core\api\applications\IApplication;
use mfe\core\libs\components\CException;

/**
 * Interface IApplicationManager
 *
 * @package mfe\core\api\applications\managers;
 */
interface IApplicationManager
{
    /**
     * @return IApplication|null
     */
    public function application();

    /**
     * @param IApplication|string $application
     *
     * @return $this
     * @throws CException
     */
    public function registerApplication($application);

    /**
     * @param IApplication|string $application
     *
     * @return $this
     * @throws CException
     */
    public function registerAsDefault($application);

    /**
     * @param IApplication|string $application
     *
     * @return $this
     */
    public function removeApplication($application);

    /**
     * @param IApplication|string $applicationName
     *
     * @return $this
     * @throws CException
     */
    public function loadApplication($applicationName);

    /**
     * @param IApplication|string $applicationName
     * @param bool $loadDefaultApplication
     *
     * @return $this
     * @throws CException
     */
    public function unloadApplication($applicationName, $loadDefaultApplication = true);
}

