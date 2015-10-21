<?php namespace mfe\core\applications;

use ArrayObject;
use InvalidArgumentException;
use mfe\core\api\applications\IHybridApplication;
use mfe\core\libs\applications\CApplication;
use mfe\core\libs\components\CException;
use mfe\core\libs\managers\CRouteManager;
use mfe\core\libs\system\Stream;
use mfe\core\MfE;
use Psr\Http\Message\ResponseInterface;
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

    /** @var ArrayObject|array */
    static protected $config;

    /**
     * @setup
     *
     * @return void
     * @throws CException
     */
    public function setup()
    {
        // TODO:: load & import configs
        static::$config = MfE::app()->importInitConfig(__CLASS__);
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function load()
    {
        $this->events->on('application.request',
            function (CApplication $application) {
//                if ($request->isSocket) {
//                    $packetManager = new NetworkPacketManager($request->data);
//                    $packetManager->addNamespace(__NAMESPACE__ . '/' . self::APPLICATION_TYPE);
//                    $packetManager->Init();
//                    return;
//                }

                $router = new CRouteManager(
                // (static::$config->router) ?: null,
                // (static::$config->router && static::$config->router->rules) ?: null
                );
                $router->addNamespace(__NAMESPACE__ . '/' . self::APPLICATION_TYPE);
                $router->run($application->request->getUri(), $application->response);

                $text = new Stream('php://memory', 'w+');
                $text->write('Hello from Application' . PHP_EOL);

                /** @var ResponseInterface $response */
                $application->response = $application->response->withBody($text);
            }
        );
    }
}
