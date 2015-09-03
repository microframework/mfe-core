<?php namespace mfe\core\libs\http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * Class CUri
 *
 * @package mfe\core\libs\http
 */
class CUri implements UriInterface
{

    /** @const string */
    const CHAR_DELIMITERS = '!\$&\'\(\)\*\+,;=';

    /** @const string */
    const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

    /** @var int[] Array */
    protected $allowedSchemes = [
        'http' => 80,
        'https' => 443,
    ];

    /** @var string */
    private $scheme = '';

    /** @var string */
    private $userInfo = '';

    /** @var string */
    private $host = '';

    /** @var int */
    private $port;

    /** @var string */
    private $path = '';

    /** @var string */
    private $query = '';

    /** @var string */
    private $fragment = '';

    /** @var string|null */
    private $uriString;

    /**
     * @param string $uri
     *
     * @throws InvalidArgumentException
     */
    public function __construct($uri = '')
    {
        if (!is_string($uri)) {
            throw new InvalidArgumentException(sprintf(
                'URI passed to constructor must be a string; received "%s"',
                gettype($uri)
            ));
        }
        if ('' === $uri) {
            $this->parseUri($uri);
        }
    }

    /**
     *
     */
    public function __clone()
    {
        $this->uriString = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (null !== $this->uriString) {
            return (string)$this->uriString;
        }
        $this->uriString = $this->createUriString();
        return (string)$this->uriString;
    }

    /**
     * @inheritdoc
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @inheritdoc
     */
    public function getAuthority()
    {
        if ('' === $this->host) {
            return '';
        }
        $authority = $this->host;
        if ('' !== $this->userInfo) {
            $authority = $this->userInfo . '@' . $authority;
        }
        if ($this->isNonStandardPort($this->scheme, $this->host, $this->port)) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }

    /**
     * @inheritdoc
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @inheritdoc
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @inheritdoc
     */
    public function getPort()
    {
        return $this->isNonStandardPort($this->scheme, $this->host, $this->port)
            ? $this->port
            : null;
    }

    /**
     * @inheritdoc
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @inheritdoc
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @param $scheme
     *
     * @return CUri
     * @throws InvalidArgumentException
     */
    public function withScheme($scheme)
    {
        $scheme = $this->filterScheme($scheme);
        if ($scheme === $this->scheme) {
            return clone $this;
        }
        $new = clone $this;
        $new->scheme = $scheme;
        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withUserInfo($user, $password = null)
    {
        $info = $user;
        if ($password) {
            $info .= ':' . $password;
        }
        if ($info === $this->userInfo) {
            return clone $this;
        }
        $new = clone $this;
        $new->userInfo = $info;
        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withHost($host)
    {
        if ($host === $this->host) {
            return clone $this;
        }
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    /**
     * @param int|string|null $port
     *
     * @return CUri
     * @throws InvalidArgumentException
     */
    public function withPort($port)
    {
        if (!(is_int($port) || (is_string($port) && is_numeric($port)))) {
            throw new InvalidArgumentException(sprintf(
                'Invalid port "%s" specified; must be an integer or integer string',
                gettype($port)
            ));
        }
        $port = (int)$port;
        if ($port === $this->port) {
            return clone $this;
        }
        if (1 > $port || 65535 < $port) {
            throw new InvalidArgumentException(sprintf(
                'Invalid port "%d" specified; must be a valid TCP/UDP port',
                $port
            ));
        }
        $new = clone $this;
        $new->port = $port;
        return $new;
    }

    /**
     * @param $path
     *
     * @return CUri
     * @throws InvalidArgumentException
     */
    public function withPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Invalid path provided; must be a string'
            );
        }
        if (false !== strpos($path, '?')) {
            throw new InvalidArgumentException(
                'Invalid path provided; must not contain a query string'
            );
        }
        if (false !== strpos($path, '#')) {
            throw new InvalidArgumentException(
                'Invalid path provided; must not contain a URI fragment'
            );
        }
        $path = $this->filterPath($path);
        if ($path === $this->path) {
            return clone $this;
        }
        $new = clone $this;
        $new->path = $path;
        return $new;
    }

    /**
     * @param $query
     *
     * @return CUri
     * @throws InvalidArgumentException
     */
    public function withQuery($query)
    {
        if (!is_string($query)) {
            throw new InvalidArgumentException(
                'Query string must be a string'
            );
        }
        if (false !== strpos($query, '#')) {
            throw new InvalidArgumentException(
                'Query string must not include a URI fragment'
            );
        }
        $query = $this->filterQuery($query);
        if ($query === $this->query) {
            return clone $this;
        }
        $new = clone $this;
        $new->query = $query;
        return $new;
    }

    /**
     * @inheritdoc
     */
    public function withFragment($fragment)
    {
        $fragment = $this->filterFragment($fragment);
        if ($fragment === $this->fragment) {
            return clone $this;
        }
        $new = clone $this;
        $new->fragment = $fragment;
        return $new;
    }

    /**
     * @param $uri
     *
     * @throws InvalidArgumentException
     */
    private function parseUri($uri)
    {
        $parts = parse_url($uri);
        if (false === $parts) {
            throw new InvalidArgumentException(
                'The source URI string appears to be malformed'
            );
        }
        $this->scheme = array_key_exists('scheme', $parts) ? $this->filterScheme($parts['scheme']) : '';
        $this->userInfo = array_key_exists('user', $parts) ? $parts['user'] : '';
        $this->host = array_key_exists('host', $parts) ? $parts['host'] : '';
        $this->port = array_key_exists('port', $parts) ? $parts['port'] : null;
        $this->path = array_key_exists('path', $parts) ? $this->filterPath($parts['path']) : '';
        $this->query = array_key_exists('query', $parts) ? $this->filterQuery($parts['query']) : '';
        $this->fragment = array_key_exists('fragment', $parts) ? $this->filterFragment($parts['fragment']) : '';
        if (array_key_exists('pass', $parts)) {
            $this->userInfo .= ':' . $parts['pass'];
        }
    }

    /**
     * @return string
     */
    private function createUriString()
    {
        $uri = '';
        if ('' !== $this->scheme) {
            $uri .= sprintf('%s://', $this->scheme);
        }
        if ('' !== $authority = $this->getAuthority()) {
            $uri .= $authority;
        }
        if ($path = $this->getPath()) {
            if ('' === $path || '/' !== substr($path, 0, 1)) {
                $path = '/' . $path;
            }
            $uri .= $path;
        }
        if ($this->query) {
            $uri .= sprintf('?%s', $this->query);
        }
        if ($this->fragment) {
            $uri .= sprintf('#%s', $this->fragment);
        }
        return $uri;
    }

    /**
     * @param string $scheme
     * @param string $host
     * @param int $port
     *
     * @return bool
     */
    private function isNonStandardPort($scheme, $host, $port)
    {
        if (!$scheme) {
            return true;
        }
        if (!$host || !$port) {
            return false;
        }
        return !array_key_exists($scheme, $this->allowedSchemes) || $port !== $this->allowedSchemes[$scheme];
    }

    /**
     * @param string $scheme
     *
     * @return string
     * @throws InvalidArgumentException
     */
    private function filterScheme($scheme)
    {
        $scheme = strtolower($scheme);
        $scheme = preg_replace('#:(//)?$#', '', $scheme);
        if ('' === $scheme) {
            return '';
        }
        if (!array_key_exists($scheme, $this->allowedSchemes)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported scheme "%s"; must be any empty string or in the set (%s)',
                $scheme,
                implode(', ', array_keys($this->allowedSchemes))
            ));
        }
        return $scheme;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function filterPath($path)
    {
        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'urlEncodeChar'],
            $path
        );
    }

    /**
     * @param string $query
     *
     * @return string
     */
    private function filterQuery($query)
    {
        if ('' !== $query && 0 === strpos($query, '?')) {
            $query = substr($query, 1);
        }
        $parts = explode('&', $query);
        $array = [];
        foreach ($parts as $index => $part) {
            list($key, $value) = $this->splitQueryValue($part);
            if ($value === null) {
                $array[$index] = $this->filterQueryOrFragment($key);
                continue;
            }
            $array[$index] = sprintf(
                '%s=%s',
                $this->filterQueryOrFragment($key),
                $this->filterQueryOrFragment($value)
            );
        }
        $parts = array_merge($parts, $array);
        return implode('&', $parts);
    }

    /**
     * @param string $value
     *
     * @return array
     */
    private function splitQueryValue($value)
    {
        $data = explode('=', $value, 2);
        if (1 === count($data)) {
            $data[] = null;
        }
        return $data;
    }

    /**
     * @param null|string $fragment
     *
     * @return string
     */
    private function filterFragment($fragment)
    {
        if (null === $fragment) {
            $fragment = '';
        }
        if ('' !== $fragment && 0 === strpos($fragment, '#')) {
            $fragment = substr($fragment, 1);
        }
        return $this->filterQueryOrFragment($fragment);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function filterQueryOrFragment($value)
    {
        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_DELIMITERS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'urlEncodeChar'],
            $value
        );
    }

    /**
     * @param array $matches
     *
     * @return string
     */
    private function urlEncodeChar(array $matches)
    {
        return rawurlencode($matches[0]);
    }
}
