#!/usr/bin/env php
<?php

$socket = stream_socket_server('tcp://0.0.0.0:8080', $error_number, $error);

if (!$socket) {
    die("{$error} ({$error_number})" . PHP_EOL);
}

$connects = [];
while (true) {
    //формируем массив прослушиваемых сокетов:
    $read = $connects;
    $read[] = $socket;
    $write = $except = null;

    if (!stream_select($read, $write, $except, null)) {//ожидаем сокеты доступные для чтения (без таймаута)
        break;
    }

    if (in_array($socket, $read, true)) {//есть новое соединение
        $connect = stream_socket_accept($socket, -1);//принимаем новое соединение
        $connects[] = $connect;//добавляем его в список необходимых для обработки
        unset($read[array_search($socket, $read)]);
        $read = array_values($read);
    }

    foreach ($read as $connect) {//обрабатываем все соединения
        $headers = '';
        while ($buffer = rtrim(fgets($connect, 1024))) {
            if (4096 <= strlen($headers)) {
                break;
            }
            $headers .= $buffer . PHP_EOL;
            //TODO::
        }
        //echo $headers . PHP_EOL;
        //var_dump($connects);
        fwrite($connect,
            'HTTP/1.1 200 OK' . PHP_EOL .
            'Content-Type: text/html;charset=utf-8' . PHP_EOL .
            'Connection: close' . PHP_EOL . PHP_EOL .
            'Привет'
        );
        fclose($connect);
        unset($connects[array_search($connect, $connects)]);
        $connects = array_values($connects);
    }
}

fclose($socket);
