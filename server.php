<?php

use mfe\core\libs\configs\CServerConfig;
use mfe\core\libs\http\server\HttpServer;
use mfe\core\libs\http\server\middleware\ApplicationServer;
use mfe\core\libs\http\server\middleware\StaticServer;
use mfe\core\libs\http\server\StreamServer;
use mfe\core\libs\http\server\upgrades\WebSocketServer;
use mfe\core\MfE;

(defined('MFE_SERVER')) or define('MFE_SERVER', true);

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

require_once __DIR__ . '/src/MfE.php';

$ip = '0.0.0.0';
$port = 8000;

if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    $helpScreen = 'MfE Simple Server (v.' . MfE::ENGINE_VERSION . ')' . PHP_EOL;
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

$server = new StreamServer(HttpServer::build([
    WebSocketServer::class
], [
    StaticServer::class,
    ApplicationServer::class
]), $config = [
    'document_root' => __DIR__ . '/web',
    'document_index' => 'index.html',
    'application' => 'DefaultApplication'
]);

var_dump(CServerConfig::fromFile('server.cfg'));

$server->listen("{$ip}:{$port}");
