<?php namespace mfe\core\api\http;

/**
 * Interface IMiddlewareServer
 *
 * @package mfe\core\api\http
 */
interface IMiddlewareServer
{
    /**
     * @param IHttpSocketReader $reader
     * @param IHttpSocketWriter $writer
     *
     * @return bool
     */
    public function request(IHttpSocketReader $reader, IHttpSocketWriter $writer);
}
