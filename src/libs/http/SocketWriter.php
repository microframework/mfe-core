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
    private $isEncoded = false;

    /**
     * @param resource $connect
     * @param resource[] $connects
     * @param bool $isEncoded
     */
    public function __construct($connect, &$connects, $isEncoded = false)
    {
        $this->connect = $connect;
        $this->connects = $connects;
        $this->isEncoded = $isEncoded;
    }

    public function send($data)
    {
        if ('' === trim($data)) return;
        if (!$this->isEncoded) {
            fwrite($this->connect,
                'HTTP/1.1 200 OK' . static::EOL .
                'Content-Type: text/html;charset=utf-8' . static::EOL .
                'Connection: close' . static::EOL . static::EOL .

                $data
            );
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
