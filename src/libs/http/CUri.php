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
    const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

    /** @const string */
    const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

    /** @var int[] */
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
            /** @var object $uri */
            throw new InvalidArgumentException(sprintf(
                'URI passed to constructor must be a string; received "%s"',
                (is_object($uri) ? get_class($uri) : gettype($uri))
            ));
        }
        if ('' === $uri) {
            $this->parseUri($uri);
        }
    }

    /**
     * @clone
     */
    public function __clone()
    {
        $this->uriString = null;
    }

    /**
     * @return null|string
     */
    public function __toString()
    {
        if (null !== $this->uriString) {
            return (string)$this->uriString;
        }
        $this->uriString = static::createUriString(
            $this->scheme,
            $this->getAuthority(),
            $this->getPath(),
            $this->query,
            $this->fragment
        );
        return (string)$this->uriString;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
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
     * @return string
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int|null
     */
    public function getPort()
    {
        return $this->isNonStandardPort($this->scheme, $this->host, $this->port)
            ? $this->port
            : null;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @param string $scheme
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function withScheme($scheme)
    {
        if (!is_string($scheme)) {
            /** @var object $scheme */
            throw new InvalidArgumentException(sprintf(
                '%s expects a string argument; received %s', __METHOD__,
                (is_object($scheme) ? get_class($scheme) : gettype($scheme))
            ));
        }
        $scheme = $this->filterScheme($scheme);
        if ($scheme === $this->scheme) {
            return clone $this;
        }
        $new = clone $this;
        $new->scheme = $scheme;
        return $new;
    }

    /**
     * @param null|string $user
     * @param null|string $password
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function withUserInfo($user, $password = null)
    {
        if (!is_string($user)) {
            /** @var object $user */
            throw new InvalidArgumentException(sprintf(
                '%s expects a string user argument; received %s', __METHOD__,
                (is_object($user) ? get_class($user) : gettype($user))
            ));
        }
        if (null !== $password && !is_string($password)) {
            /** @var object $password */
            throw new InvalidArgumentException(sprintf(
                '%s expects a string password argument; received %s', __METHOD__,
                (is_object($password) ? get_class($password) : gettype($password))
            ));
        }
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
     * @param string $host
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function withHost($host)
    {
        if (!is_string($host)) {
            /** @var object $host */
            throw new InvalidArgumentException(sprintf(
                '%s expects a string argument; received %s', __METHOD__,
                (is_object($host) ? get_class($host) : gettype($host))
            ));
        }
        if ($host === $this->host) {
            return clone $this;
        }
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    /**
     * @param int $port
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function withPort($port)
    {
        if (!is_numeric($port)) {
            /** @var object $port */
            throw new InvalidArgumentException(sprintf(
                'Invalid port "%s" specified; must be an integer or integer string',
                (is_object($port) ? get_class($port) : gettype($port))
            ));
        }
        $port = (int)$port;
        if ($port === $this->port) {
            return clone $this;
        }
        if ($port < 1 || $port > 65535) {
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
     * @param string $path
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function withPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Invalid path provided; must be a string'
            );
        }
        if (strpos($path, '?') !== false) {
            throw new InvalidArgumentException(
                'Invalid path provided; must not contain a query string'
            );
        }
        if (strpos($path, '#') !== false) {
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
     * @return static
     * @throws InvalidArgumentException
     */
    public function withQuery($query)
    {
        if (!is_string($query)) {
            throw new InvalidArgumentException(
                'Query string must be a string'
            );
        }
        if (strpos($query, '#') !== false) {
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
     * @param string $fragment
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function withFragment($fragment)
    {
        if (!is_string($fragment)) {
            /** @var object $fragment */
            throw new InvalidArgumentException(sprintf(
                '%s expects a string argument; received %s', __METHOD__,
                (is_object($fragment) ? get_class($fragment) : gettype($fragment))
            ));
        }
        $fragment = $this->filterFragment($fragment);
        if ($fragment === $this->fragment) {
            return clone $this;
        }
        $new = clone $this;
        $new->fragment = $fragment;
        return $new;
    }

    /**
     * @param string $uri
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
     * @param string $scheme
     * @param string $authority
     * @param string $path
     * @param string $query
     * @param string $fragment
     *
     * @return string
     */
    private static function createUriString($scheme, $authority, $path, $query, $fragment)
    {
        $uri = '';
        if ('' !== $scheme) {
            $uri .= sprintf('%s://', $scheme);
        }
        if ('' !== $authority) {
            $uri .= $authority;
        }
        if ($path) {
            if ('' === $path || '/' !== substr($path, 0, 1)) {
                $path = '/' . $path;
            }
            $uri .= $path;
        }
        if ($query) {
            $uri .= sprintf('?%s', $query);
        }
        if ($fragment) {
            $uri .= sprintf('#%s', $fragment);
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
        $path = preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'urlEncodeChar'],
            $path
        );
        if ('' === $path) {
            return $path;
        }
        if ('/' !== $path[0]) {
            return $path;
        }
        return '/' . ltrim($path, '/');
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
        if (null !== $fragment && 0 === strpos($fragment, '#')) {
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
            '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
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
