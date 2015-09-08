<?php namespace mfe\core\libs\http;

use InvalidArgumentException;
use mfe\core\libs\helpers\CHttpSecurityHelper;
use mfe\core\libs\system\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class TRequest
 *
 * @property array $headerNames
 * @property array $headers
 * @property Stream $stream
 *
 * @method filterHeaders(array $headers)
 * @method hasHeader()
 *
 * @package mfe\core\libs\http
 */
trait TRequest
{
    /** @var string */
    private $method = '';

    /** @var null|string */
    private $requestTarget;

    /** @var UriInterface|CUri|null */
    private $uri;

    /**
     * @param null|string|UriInterface $uri
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
        $this->method = $method ?: '';
        $this->uri = $uri ?: new CUri();
        $this->stream = ($body instanceof StreamInterface) ? $body : new Stream($body, 'wb+');
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
        if ('' === $target || null === $target) {
            $target = '/';
        }
        return $target;
    }

    /**
     * @param mixed $requestTarget
     *
     * @return static
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
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return static
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
     * @param bool $preserveHost
     *
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $new = clone $this;
        $new->uri = $uri;
        if ($preserveHost && $this->hasHeader('Host')) {
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
        $new->headers['Host'] = [$host];
        return $new;
    }

    /**
     * @param null|string $method
     *
     * @throws InvalidArgumentException
     */
    private function validateMethod($method)
    {
        if (null === $method) {
            return;
        }
        if (!is_string($method)) {
            /** @var object $method */
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method; must be a string, received %s',
                (is_object($method) ? get_class($method) : gettype($method))
            ));
        }
        if (!preg_match('/^[!#$%&\'*+.^_`\|~0-9a-z-]+$/i', $method)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method "%s" provided',
                $method
            ));
        }
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
