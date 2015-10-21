<?php namespace mfe\core\libs\http\server\middleware;

use ArrayObject;
use InvalidArgumentException;
use mfe\core\api\applications\IHybridApplication;
use mfe\core\api\http\IHttpSocketReader;
use mfe\core\api\http\IHttpSocketWriter;
use mfe\core\api\http\IMiddlewareServer;
use mfe\core\libs\applications\CApplication;
use mfe\core\libs\components\CException;
use mfe\core\libs\handlers\CRequestFactory;
use mfe\core\libs\http\HttpSocketWriter;
use mfe\core\MfE;

/**
 * Class ApplicationServer
 *
 * @package mfe\core\libs\http\server\middleware
 */
class ApplicationServer implements IMiddlewareServer
{
    protected $application;

    /**
     * @param ArrayObject $config
     */
    public function __construct(ArrayObject $config)
    {
        if (isset($config->http->application->application)) {
            $this->application = $config->http->application->application;
        }
    }

    /**
     * @param IHttpSocketReader $reader
     * @param IHttpSocketWriter|HttpSocketWriter $writer
     *
     * @return bool
     * @throws CException
     * @throws InvalidArgumentException
     */
    public function request(IHttpSocketReader $reader, IHttpSocketWriter $writer)
    {
        ob_start();

        /** @var IHybridApplication|CApplication $application */
        $application = MfE::getInstance()->getApplicationManager()
            ->registerApplication($this->application)
            ->loadApplication($this->application)
            ->application();

        $application->request = CRequestFactory::fromGlobals();
        $application->run();

        $application->events->trigger('application.request', [$application]);

        $content = ob_get_contents();
        ob_end_clean();

        fwrite(STDOUT, $content, strlen($content));

        // TODO TO EMITTER!!!

        $writer->send($application->response->getBody());
        echo $content;
        return true;
    }
}
