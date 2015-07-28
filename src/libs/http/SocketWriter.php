<?php namespace mfe\core\libs\http;

/**
 * Class SocketWriter
 *
 * @package mfe\core\libs\http
 */
class SocketWriter
{
    /**
     * @param resource $connect
     */
    public function __construct($connect)
    {
        fwrite($connect,
            'HTTP/1.1 200 OK' . PHP_EOL .
            'Content-Type: text/html;charset=utf-8' . PHP_EOL .
            'Connection: close' . PHP_EOL . PHP_EOL .
            'Привет'
        );
    }
}
