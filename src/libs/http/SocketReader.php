<?php namespace mfe\core\libs\http;

/**
 * Class SocketReader
 *
 * @package mfe\core\libs\http
 */
class SocketReader
{
    const EOL = "\r\n";

    public $isWebSocket = false;
    public $isClose = false;
    public $keepAlive = false;

    public $headers = [];
    public $info = [];
    public $data = '';

    private $connect;

    /**
     * @param resource $connect
     */
    public function __construct($connect)
    {
        $this->connect = $connect;
    }

    public function getHeaders()
    {
        while (strlen($buffer = rtrim(fread($this->connect, 1024))) || !feof($this->connect)) {
            foreach (explode(static::EOL, $buffer) as $i => $line) {
                if ($i === 0) {
                    if ('' === $line) {
                        $this->isClose = true;
                        break;
                    }
                    $line = explode(' ', $line);
                    $this->info = [
                        'method' => $line[0],
                        'uri' => $line[1],
                        'protocol' => $line[2]
                    ];
                } elseif (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                    $this->headers[$matches[1]] = $matches[2];
                }
            }
            if (stream_get_meta_data($this->connect)['unread_bytes'] <= 0) break;
        }

        $address = explode(':', stream_socket_get_name($this->connect, true));
        $this->info['ip'] = $address[0];
        $this->info['port'] = $address[1];

        $this->tryKeepAlive();
        $this->tryWebsocketUpgrade();
    }

    public function getData()
    {
        $data = fread($this->connect, 100000);
        if (!$data) {
            $this->isClose = true;
            return false;
        }
        return $this->data = WebSocketHelper::decode($data)['payload'];
    }

    protected function tryKeepAlive()
    {
        if (isset($this->headers['Expect']) && '100-continue' === $this->headers['Expect']) {
            $this->isClose = true;
            return $this->keepAlive = false;
        }
        if (isset($this->headers['Connection']) && 'keep-alive' === $this->headers['Connection']) {
            return $this->keepAlive = true;
        }
        return $this->isClose = true;
    }

    protected function tryWebsocketUpgrade()
    {
        if (empty($this->headers['Sec-WebSocket-Key'])) {
            return false;
        }

        $SecWebSocketAccept = base64_encode(
            pack(
                'H*',
                sha1($this->headers['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
            )
        );
        $upgrade =
            "HTTP/1.1 101 Web Socket Protocol Handshake" . static::EOL .
            "Upgrade: websocket" . static::EOL .
            "Connection: Upgrade" . static::EOL .
            "Sec-WebSocket-Accept: {$SecWebSocketAccept}" . static::EOL . static::EOL;
        fwrite($this->connect, $upgrade);
        $this->isClose = false;
        return $this->isWebSocket = true;
    }
}
