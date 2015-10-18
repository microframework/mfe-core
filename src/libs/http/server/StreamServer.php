<?php namespace mfe\core\libs\http\server;

use ArrayObject;
use mfe\core\api\configs\IConfig;
use mfe\core\api\http\ITcpServer;
use mfe\core\libs\http\server\exceptions\StreamServerException;

/**
 * Class StreamServer
 *
 * @package mfe\core\libs\http\server
 */
class StreamServer
{
    use TStreamServer;

    /** @var ArrayObject|array */
    private $config;

    /** @var array */
    private $builder;

    /** @var ITcpServer[] */
    private $buildStack = [];

    /**
     * @param string $builder
     * @param IConfig $config
     */
    public function __construct($builder, IConfig $config)
    {
        $this->config = new ArrayObject($config, ArrayObject::ARRAY_AS_PROPS);
        $this->builder = $builder;
    }

    /**
     * @param $socketBind
     *
     * @throws StreamServerException
     */
    public function listen($socketBind)
    {
        $bindAddress = explode(':', $socketBind);
        fwrite(STDOUT, "Server started at: http://{$socketBind}/" . PHP_EOL);

        $this->listenStreamServer($bindAddress[0], $bindAddress[1]);
        stream_set_timeout($this->server, 5);
        $this->acceptSockets();
        $this->closeStreamServer();
    }

    /**
     * @param resource $socket
     */
    protected function handleSocket($socket)
    {
        if (!array_key_exists((int)$socket, $this->buildStack)) {
            $this->buildStack[(int)$socket] = new $this->builder['_CLASS']($socket, [
                'upgrades' => $this->builder['upgrades'],
                'middleware' => $this->builder['middleware']
            ], $this->config);
        } else {
            $this->buildStack[(int)$socket]->handle($socket);
        }

        if ($this->buildStack[(int)$socket]->isClose) {
            if ($socket && 'stream' === get_resource_type($socket)) {
                $this->closeSocket($socket);
            }
            unset($this->buildStack[(int)$socket]);
        }
    }
}
