<?php namespace mfe\core\libs\http\server;

use mfe\core\libs\managers\CEventManager;
use mfe\core\MfE;

/**
 * Class HttpServer
 *
 * @package mfe\core\libs\http\server
 */
class HttpServer
{
    const VERSION = '0.0.1';

    const ENV_IP = '0.0.0.0';
    const ENV_PORT = '8000';

    public $keepAliveEnable = true;

    public $bind_ip;
    public $bind_port;

    public $error = [
        'type' => 0,
        'message' => ''
    ];

    /** @var resource */
    private $server;

    /** @var CEventManager */
    private $events;

    /** @var array */
    private $connects = [];

    /** @var array */
    private $upgradedConnects = [];

    public function __construct($ip = self::ENV_IP, $port = self::ENV_PORT)
    {
        $this->events = MfE::app()->events;

        $this->bind_ip = $ip;
        $this->bind_port = $port;
    }

    public function __destruct()
    {
        $this->events->trigger('server.stop');
        fwrite(STDOUT, 'Server stopped.' . PHP_EOL);
    }

    protected function setup()
    {
        //stream_set_chunk_size($this->server, 1024);
        //stream_set_blocking($this->server, 0);
        stream_set_timeout($this->server, 5);

        $this->events->trigger('server.start', [$this->server]);
        fwrite(STDOUT, "Server started at: {$this->bind_ip}:{$this->bind_port}." . PHP_EOL);

        return $this;
    }

    public function run()
    {
        $this->server = stream_socket_server(
            "tcp://{$this->bind_ip}:{$this->bind_port}",
            $this->error['type'],
            $this->error['message']
        );

        if (!$this->server) {
            throw new HttpServerException("{$this->error['type']} ({$this->error['message']})" . PHP_EOL);
        }

        $this->setup()->loop();

        fclose($this->server);
    }

    protected function loop()
    {
        while (true) {
            $connections = $this->connects;
            $connections[(int)$this->server] = $this->server;
            $write = $except = null;

            if (!stream_select($connections, $write, $except, null)) {
                break;
            }

            if (in_array($this->server, $connections)) {
                $connect = stream_socket_accept($this->server, -1);
                $this->connects[(int)$connect] = $connect;
                unset($connections[(int)$this->server]);
            }

            $this->processConnection($connections);
        }
    }

    protected function processConnection(array $connections)
    {
        foreach ($connections as $connect) {
            $firstConnect = false;
            $reader = new SocketReader($connect);
            $writer = new SocketWriter($connect, $this->upgradedConnects);

            if (array_key_exists((int)$connect, $this->upgradedConnects)) {
                $reader->isWebSocket = true;
                $writer->isEncoded = true;
                $reader->getData();
            }

            while ((!$reader->isClose || !$firstConnect) && !$reader->isWebSocket && !feof($connect)) {
                if (!array_key_exists(intval($connect), $this->upgradedConnects)) {
                    $firstConnect = true;
                    $reader->getHeaders();
                }

                if ($reader->isWebSocket && !array_key_exists((int)$connect, $this->upgradedConnects)) {
                    $this->upgradedConnects[(int)$connect] = $connect;
                    $writer->isEncoded = true;
                }
                if ($this->keepAliveEnable && $reader->keepAlive) {
                    $writer->keepAlive = true;
                } elseif(!$reader->isWebSocket) {
                    $reader->isClose = true;
                }

                if ($firstConnect) {
                    $this->events->trigger('server.connection.open', [$reader, $writer, $connect]);
                }
                if ($firstConnect && !$reader->isWebSocket) {
                    $this->events->trigger('server.connection.data', [$reader, $writer, $connect]);
                }
            }

            if (!$firstConnect && $reader->isWebSocket) {
                $this->events->trigger('server.connection.data', [$reader, $writer, $connect]);
            }

            if ($reader->isClose) {
                if (array_key_exists((int)$connect, $this->upgradedConnects)) {
                    unset($this->upgradedConnects[(int)$connect]);
                }
                unset($this->connects[(int)$connect]);
                fclose($connect);
                $this->events->trigger('server.connection.close', [$reader, $writer]);
            }
        }
    }
}
