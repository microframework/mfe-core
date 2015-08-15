<?php namespace mfe\core\libs\http;

/**
 * Class SocketWriter
 *
 * @package mfe\core\libs\http
 */
class SocketWriter
{
    const EOL = "\r\n";

    private $connect;
    private $connects = [];
    public $isEncoded = false;
    public $keepAlive = false;

    /**
     * @param resource $connect
     * @param resource[] $connects
     */
    public function __construct($connect, &$connects)
    {
        $this->connect = $connect;
        $this->connects = &$connects;
    }

    public function send($data)
    {
        if ('' === trim($data)) return;
        if (!$this->isEncoded) {
            $response = 'HTTP/1.1 200 OK' . static::EOL .
                'Content-Type: text/html;charset=utf-8' . static::EOL .
                'Content-Length: ' . strlen($data) . static::EOL .
                'Connection: ' . ((!$this->keepAlive) ? 'close' : 'keep-alive') .
                static::EOL . static::EOL .

                $data;

            fwrite($this->connect, $response);
        } else {
            fwrite($this->connect, WebSocketHelper::encode($data));
        }
    }

    public function broadcast($data, $excludeSelf = false)
    {
        if ($this->isEncoded && '' !== trim($data)) {
            foreach ($this->connects as $connect) {
                if (!($excludeSelf && $connect === $this->connect)) {
                    fwrite($connect, WebSocketHelper::encode($data));
                }
            }
        }
    }
}
