<?php namespace mfe\core\api\applications;

use mfe\core\api\applications\managers\IApplicationManager;
use mfe\core\api\components\managers\IComponentManager;
use mfe\core\api\events\managers\IEventsManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface IApplication
 *
 * @property IApplicationManager $applications
 * @property IComponentManager $di
 * @property IEventsManager $events
 *
 * @property ServerRequestInterface $request
 * @property ResponseInterface $response
 *
 * @package mfe\core\api\applications
 */
interface IApplication
{
    public function load();

    public function unload();
}
