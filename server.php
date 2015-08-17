<?php

use mfe\core\api\events\managers\IEventsManager;
use mfe\core\libs\http\server\HttpServer;
use mfe\core\libs\http\server\SocketReader;
use mfe\core\libs\http\server\SocketWriter;
use mfe\core\MfE;

require_once __DIR__ . '/src/MfE.php';

$ip = '0.0.0.0';
$port = 8000;

if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    $helpScreen = 'MfE Simple Server (v.' . HttpServer::VERSION . ')' . PHP_EOL;
    $helpScreen .= PHP_EOL;
    $helpScreen .= 'Usage: server --ip=127.0.0.1 --port=80' . PHP_EOL;

    $helpScreen .= str_pad('  --ip', 18, ' ') . str_pad('Bind ip address.', 62, ' ') . PHP_EOL;
    $helpScreen .= str_pad('  --port', 18, ' ') . str_pad('Bind port.', 62, ' ') . PHP_EOL;
    $helpScreen .= PHP_EOL;
    $helpScreen .= str_pad('  --help, -h', 18) . str_pad('Show this screen.', 62, ' ') . PHP_EOL;

    fwrite(STDOUT, $helpScreen);
    exit;
}

if (in_array('--ip', $argv, true)) {
    $ip = $argv[array_search('--ip', $argv, true) + 1];
}

if (in_array('--port', $argv, true)) {
    $port = $argv[array_search('--port', $argv, true) + 1];
}

/** @var IEventsManager $events*/
$events = MfE::app()->events;

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

$events->on('server.connection.close', function ($reader, $writer) {
    /**
     * @var SocketReader $reader
     * @var SocketWriter $writer
     */
    if ($reader->isWebSocket) {
        $writer->broadcast('Кто-то отвалился!');
    }
});

$server = new HttpServer($ip, $port);
$server->run();
