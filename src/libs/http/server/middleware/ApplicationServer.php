<?php namespace mfe\core\libs\http\server\middleware;

use ArrayObject;
use mfe\core\api\http\IHttpSocketReader;
use mfe\core\api\http\IHttpSocketWriter;
use mfe\core\api\http\IMiddlewareServer;

/**
 * Class ApplicationServer
 *
 * @package mfe\core\libs\http\server\middleware
 */
class ApplicationServer implements IMiddlewareServer
{
    /**
     * @param ArrayObject $config
     *
     * @return static
     */
    static public function setup(ArrayObject $config)
    {
        return new static;
    }

    /**
     * @param IHttpSocketReader $reader
     * @param IHttpSocketWriter $writer
     *
     * @return bool
     */
    public function request(IHttpSocketReader $reader, IHttpSocketWriter $writer)
    {
        // TODO: Implement request() method.

        return false;
    }
}
