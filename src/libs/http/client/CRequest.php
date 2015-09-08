<?php namespace mfe\core\libs\http\client;

use InvalidArgumentException;
use mfe\core\libs\http\TMessage;
use mfe\core\libs\http\TRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class CRequest
 *
 * @package mfe\core\libs\http\client
 */
class CRequest implements RequestInterface
{
    use TMessage;
    use TRequest;

    /**
     * @param null|string $uri
     * @param null|string $method
     * @param string|resource|StreamInterface $body
     * @param array $headers
     * @throws InvalidArgumentException
     */
    public function __construct($uri = null, $method = null, $body = 'php://temp', array $headers = [])
    {
        $this->initialize($uri, $method, $body, $headers);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        $headers = $this->headers;
        if (!$this->hasHeader('host')
            && ($this->uri && $this->uri->getHost())
        ) {
            $headers['Host'] = [$this->getHostFromUri()];
        }
        return $headers;
    }

    /**
     * @param string $header
     *
     * @return array
     */
    public function getHeader($header)
    {
        if (!$this->hasHeader($header)) {
            if (strtolower($header) === 'host'
                && ($this->uri && $this->uri->getHost())
            ) {
                return [$this->getHostFromUri()];
            }
            return [];
        }
        $header = $this->headerNames[strtolower($header)];
        $value = $this->headers[$header];
        return (array)$value;
    }
}
