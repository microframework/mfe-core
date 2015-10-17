<?php namespace mfe\core\api\http;

use ArrayObject;

/**
 * Interface ITcpServer
 *
 * @package mfe\core\api\http
 */
interface ITcpServer
{
    /**
     * @param array $upgrades
     * @param array $middleware
     */
    static public function build(array $upgrades, array $middleware);

    /**
     * @return void
     */
    public function run();

    /**
     * @param resource $socket
     */
    public function handle($socket);

    /**
     * @param ArrayObject $config
     *
     * @return static
     */
    public function setConfig(ArrayObject $config);
}
