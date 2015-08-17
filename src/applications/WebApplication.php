<?php namespace mfe\core\applications;

use mfe\core\api\applications\IHybridApplication;
use mfe\core\libs\base\CApplication;

use mfe\core\libs\components\CDisplay as Display;

/**
 * Class WebApplication
 *
 * @package mfe\core\applications
 */
class WebApplication extends CApplication implements IHybridApplication
{
    const APPLICATION_NAME = 'Standard default application';
    const APPLICATION_TYPE = 'WebApplication';
    const APPLICATION_VERSION = '1.0.0';

    /** @constant string, Please not modify! */
    const APPLICATION_DIR = __DIR__;

    /**
     * @setup
     */
    protected function setup()
    {

    }

    /**
     * @run
     */
    public function run()
    {
        $config = $this->config;

        $this->events->on('application.request', function ($request, $response) use ($config) {
            if ($request->isSocket) {
                $packetManager = new NetworkPacketManager($request->data);
                $packetManager->addNamespace(__NAMESPACE__ . '/' . self::APPLICATION_TYPE);
                $packetManager->Init();
                return true;
            }

            $router = new RouterManager($config->router, $config->router->rules);
            $router->addNamespace(__NAMESPACE__ . '/' . self::APPLICATION_TYPE);
            $router->run($request->url, $response);
            return true;
        });

        $this->events->on('application.response', function ($request, $response) {
            if ($request->isSocket) {
                $response->send(new Display($response->data, Display::TYPE_BINARY));
                return true;
            }

            $response->send(new Display($response->data, Display::TYPE_HTML5, 'utf-8'));
            return true;
        });
    }
}
