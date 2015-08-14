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

            if (in_array($this->server, $connections)) {
                $connect = stream_socket_accept($this->server, -1);
                $this->connects[intval($connect)] = $connect;
                unset($connections[array_search($this->server, $connections)]);
            }

            $this->processConnection($connections);
        }
    }

    protected function processConnection(array $connections)
    {
        foreach ($connections as $connect) {
            $firstConnect = false;
            $reader = new SocketReader($connect);

            if (!array_key_exists(intval($connect), $this->upgradedConnects)) {
                $reader->getHeaders();
                if ($reader->isWebSocket) {
                    $this->upgradedConnects[intval($connect)] = $connect;
                }
                $firstConnect = true;
            } else {
                $reader->isWebSocket = true;
                $reader->getData();
            }

            $writer = new SocketWriter($connect, $this->upgradedConnects, $reader->isWebSocket);

            if (!$reader->isClose) {
                if ($firstConnect) {
                    $this->events->trigger('server.connection.open', [$reader, $writer, $connect]);
                }
                if ((!$firstConnect && $reader->isWebSocket) ||
                    ($firstConnect && !$reader->isWebSocket)
                ) {
                    $this->events->trigger('server.connection.data', [$reader, $writer, $connect]);
                }

                if (!$reader->isWebSocket) {
                    $reader->isClose = true;
                }
            }

            if ($reader->isClose) {
                unset($this->upgradedConnects[intval($connect)]);
                unset($this->connects[intval($connect)]);
                fclose($connect);
                $this->events->trigger('server.connection.close', [$reader, $writer]);
            }
        }
    }
}

$events = MfE::app()->events;
$time = time();

$events->on('server.connection.open', function ($reader, $writer) {
    /**
     * @var SocketReader $reader
     * @var SocketWriter $writer
     */
    if ($reader->isWebSocket) {
        $writer->broadcast('Присоеденился новый.');
    }
});

$events->on('server.connection.data', function ($reader, $writer) {
    /**
     * @var SocketReader $reader
     * @var SocketWriter $writer
     */
    if ($reader->isWebSocket) {
        $writer->broadcast($reader->data);
    } else {
        $writer->send('Привет');
    }
});

$events->on('server.connection.close', function ($reader, $writer) use ($time) {
    /**
     * @var SocketReader $reader
     * @var SocketWriter $writer
     */
    if ($reader->isWebSocket) {
        $writer->broadcast('Кто-то отвалился!');
    }
});
