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
     * @param IApplication $application
     *
     * @return static
     * @throws CException
     */
    public function registerApplication(IApplication $application);

    /**
     * @param IApplication|string $application
     *
     * @return static
     */
    public function removeApplication($application);

    /**
     * @param IApplication $application
     *
     * @return static
     * @throws CException
     */
    public function loadApplication(IApplication $application);

    /**
     * @param IApplication $application
     * @param bool $loadDefaultApplication
     *
     * @return static
     * @throws CException
     */
    public function unloadApplication(IApplication $application, $loadDefaultApplication = true);
}

