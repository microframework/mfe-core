<?php namespace mfe\core\libs\http;

class HttpServer
{
    public $socket;

    public $bind_ip;
    public $bind_port;

    public $error = [
        'type' => 0,
        'message' => ''
    ];

    private $connects = [];

    public function __construct($ip = '127.0.0.1', $port = '8080')
    {
        $this->bind_ip = $ip;
        $this->bind_port = $port;

        $this->startServer();
    }

    public function startServer()
    {
        $this->socket = stream_socket_server(
            "tcp://{$this->bind_ip}:{$this->bind_port}",
            $this->error['type'],
            $this->error['message']
        );

        if (!$this->socket) {
            die("{$this->error['type']} ({$this->error['message']})" . PHP_EOL);
        }
    }
}
