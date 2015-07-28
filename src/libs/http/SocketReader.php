<?php namespace mfe\core\libs\http;

/**
 * Class SocketReader
 *
 * @package mfe\core\libs\http
 */
class SocketReader
{
    private $headers = '';

    /**
     * @param resource $connect
     */
    public function __construct($connect)
    {
        while ($buffer = rtrim(fgets($connect, 1024))) {
            $this->headers .= $buffer;
        }
    }
}
