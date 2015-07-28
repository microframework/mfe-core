<?php namespace mfe\core\libs\http;

use mfe\core\libs\managers\CEventManager;
use mfe\core\MfE;

/**
 * Class HttpServer
 *
 * @package mfe\core\libs\http
 */
class HttpServer
{
    const VERSION = '0.0.1';

    const ENV_IP = '0.0.0.0';
    const ENV_PORT = '8000';

    public $server;

    public $bind_ip;
    public $bind_port;

    public $error = [
        'type' => 0,
        'message' => ''
    ];

    /** @var CEventManager */
    private $events;

    /** @var array */
    private $connects = [];

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
        stream_set_chunk_size($this->server, 1024);
        stream_set_blocking($this->server, 0);

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
            $connections[] = $this->server;
            $write = $except = null;

            if (!stream_select($connections, $write, $except, null)) {
                break;
            }

            if (in_array($this->server, $connections, null)) {
                $connect = stream_socket_accept($this->server, -1);
                $this->connects[] = $connect;

                $this->events->trigger('server.connection.open', [$connect]);
            }
            unset($connections[array_search($this->server, $connections, null)]);

            $this->processConnection($connections);
        }
    }

    protected function processConnection(array $connections)
    {
        foreach ($connections as $connect) {
            $reader = new SocketReader($connect);
            $writer = new SocketWriter($connect);

            $this->events->trigger('server.connection.data', [$connect, $reader, $writer]);

            $this->events->trigger('server.connection.close', [$connect]);
            fclose($connect);

            unset($this->connects[array_search($connect, $this->connects, null)]);
        }
    }
}
