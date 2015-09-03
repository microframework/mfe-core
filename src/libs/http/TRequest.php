<?php namespace mfe\core\libs\http;

use InvalidArgumentException;
use mfe\core\libs\helpers\CHttpSecurityHelper;
use mfe\core\libs\system\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class TRequest
 *
 * @package mfe\core\libs\http
 */
trait TRequest
{
    /** @var string */
    private $method;

    /** @var null|string */
    private $requestTarget;

    /** @var null|UriInterface */
    private $uri;

    /** @var array */
    static private $validMethods = [
        'CONNECT',
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PUT',
        'TRACE',
    ];

    /**
     * @param null|string $uri
     * @param null|string $method
     * @param string|resource|StreamInterface $body
     * @param array $headers
     *
     * @throws InvalidArgumentException
     */
    private function initialize($uri = null, $method = null, $body = 'php://memory', array $headers = [])
    {
        if (!$uri instanceof UriInterface && !is_string($uri) && null !== $uri) {
            throw new InvalidArgumentException(
                'Invalid URI provided; must be null, a string, or a Psr\Http\Message\UriInterface instance'
            );
        }
        $this->validateMethod($method);
        if (!is_string($body) && !is_resource($body) && !$body instanceof StreamInterface) {
            throw new InvalidArgumentException(
                'Body must be a string stream resource identifier, '
                . 'an actual stream resource, '
                . 'or a Psr\Http\Message\StreamInterface implementation'
            );
        }
        if (is_string($uri)) {
            $uri = new CUri($uri);
        }
        $this->method = $method;
        $this->uri = $uri;

        $this->stream = ($body instanceof StreamInterface) ? $body : new Stream($body, 'r');
        list($this->headerNames, $headers) = $this->filterHeaders($headers);
        $this->assertHeaders($headers);
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getRequestTarget()
    {
        if (null !== $this->requestTarget) {
            return $this->requestTarget;
        }
        if (!$this->uri) {
            return '/';
        }
        $target = $this->uri->getPath();
        if ($this->uri->getQuery()) {
            $target .= '?' . $this->uri->getQuery();
        }
        if (empty($target)) {
            $target = '/';
        }
        return $target;
    }

    /**
     * @param mixed $requestTarget
     *
     * @return self
     * @throws InvalidArgumentException
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new InvalidArgumentException(
                'Invalid request target provided; cannot contain whitespace'
            );
        }
        $new = clone $this;
        $new->requestTarget = $requestTarget;
        return $new;
    }

    /**
     * @return string Returns
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method Case-insensitive method.
     *
     * @return self
     * @throws InvalidArgumentException
     */
    public function withMethod($method)
    {
        $this->validateMethod($method);
        $new = clone $this;
        $new->method = $method;
        return $new;
    }

    /**
     * @return UriInterface
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param UriInterface|CUri $uri
     *
     * @param bool $preserveHost
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $new = clone $this;
        $new->uri = $uri;
        if ($preserveHost) {
            return $new;
        }
        if (!$uri->getHost()) {
            return $new;
        }
        $host = $uri->getHost();
        if ($uri->getPort()) {
            $host .= ':' . $uri->getPort();
        }
        $new->headerNames['host'] = 'Host';
        $new->headers['Host'] = array($host);
        return $new;
    }

    /**
     * @param null|string $method
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    private function validateMethod($method)
    {
        if (null === $method) {
            return true;
        }
        if (!is_string($method)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method; must be a string, received %s',
                (is_object($method) ? get_class($method) : gettype($method))
            ));
        }
        $method = strtoupper($method);
        if (!in_array($method, static::$validMethods, true)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method "%s" provided',
                $method
            ));
        }
        return false;
    }

    /**
     * @return string
     */
    private function getHostFromUri()
    {
        $host = $this->uri->getHost();
        $host .= $this->uri->getPort() ? ':' . $this->uri->getPort() : '';
        return $host;
    }

    /**
     * @param array $headers
     *
     * @throws InvalidArgumentException
     */
    private function assertHeaders(array $headers)
    {
        foreach ($headers as $name => $headerValues) {
            CHttpSecurityHelper::assertValidName($name);
            array_walk($headerValues, CHttpSecurityHelper::class . '::assertValid');
        }
    }
}
