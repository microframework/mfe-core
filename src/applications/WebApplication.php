<?php namespace mfe\core\applications;

use InvalidArgumentException;
use mfe\core\api\applications\IHybridApplication;
use mfe\core\libs\applications\CApplication;

use mfe\core\libs\http\CRequest;
use mfe\core\libs\http\CResponse;
use mfe\core\libs\system\Stream;
use RuntimeException;

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
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    protected function setup()
    {
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function main()
    {
        $this->events->on('application.request',
            function (CRequest $request, CResponse $response) {
//                if ($request->isSocket) {
//                    $packetManager = new NetworkPacketManager($request->data);
//                    $packetManager->addNamespace(__NAMESPACE__ . '/' . self::APPLICATION_TYPE);
//                    $packetManager->Init();
//                    return;
//                }

//                $router = new RouterManager($this->config->router, $this->config->router->rules);
//                $router->addNamespace(__NAMESPACE__ . '/' . self::APPLICATION_TYPE);
//                $router->run($request->url, $response);

                $text = new Stream('php://memory', 'w+');
                $text->write('Hello from Application');

                $response->withBody($text);
            }
        );
    }
}
