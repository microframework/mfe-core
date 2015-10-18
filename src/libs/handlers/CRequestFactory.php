<?php namespace mfe\core\libs\handlers;

use InvalidArgumentException;
use mfe\core\api\http\IHttpSocketReader;
use mfe\core\libs\http\CRequest;
use mfe\core\libs\http\CUploadedFile;
use mfe\core\libs\http\CUri;
use mfe\core\libs\http\HttpSocketReader;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use stdClass;

/**
 * Class CRequestFactory
 *
 * @package mfe\core\libs\handlers
 */
class CRequestFactory
{
    /** @var callable */
    private static $apacheRequestHeaders = 'apache_request_headers';

    /**
     * @param array $server $_SERVER
     * @param array $query $_GET
     * @param array $body $_POST
     * @param array $cookies $_COOKIE
     * @param array $files $_FILES
     *
     * @return CRequest
     * @throws InvalidArgumentException
     */
    public static function fromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null
    )
    {
        $server = static::normalizeServer($server ?: $_SERVER);
        $files = static::normalizeFiles($files ?: $_FILES);
        $headers = static::marshalHeaders($server);
        $request = new CRequest(
            $server,
            $files,
            static::marshalUriFromServer($server, $headers),
            static::get('REQUEST_METHOD', $server, 'GET'),
            'php://input',
            $headers
        );
        return $request
            ->withCookieParams($cookies ?: $_COOKIE)
            ->withQueryParams($query ?: $_GET)
            ->withParsedBody($body ?: $_POST);
    }

    /**
     * @param string $key
     * @param array $values
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get($key, array $values, $default = null)
    {
        if (array_key_exists($key, $values)) {
            return $values[$key];
        }
        return $default;
    }

    /**
     * @param string $header
     * @param array $headers
     * @param mixed $default
     *
     * @return string
     */
    public static function getHeader($header, array $headers, $default = null)
    {
        $header = strtolower($header);
        $headers = array_change_key_case($headers, CASE_LOWER);
        if (array_key_exists($header, $headers)) {
            $value = is_array($headers[$header]) ? implode(', ', $headers[$header]) : $headers[$header];
            return $value;
        }
        return $default;
    }

    /**
     * @param array $server
     *
     * @return array
     */
    public static function normalizeServer(array $server)
    {
        $apacheRequestHeaders = self::$apacheRequestHeaders;
        if (array_key_exists('HTTP_AUTHORIZATION', $server)
            || !is_callable($apacheRequestHeaders)
        ) {
            return $server;
        }
        $array = $apacheRequestHeaders();
        if (array_key_exists('Authorization', $array)) {
            $server['HTTP_AUTHORIZATION'] = $array['Authorization'];
            return $server;
        }
        if (array_key_exists('authorization', $array)) {
            $server['HTTP_AUTHORIZATION'] = $array['authorization'];
            return $server;
        }
        return $server;
    }

    /**
     * @param array $files
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public static function normalizeFiles(array $files)
    {
        $normalized = [];
        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
                continue;
            } elseif (is_array($value)) {
                if (array_key_exists('tmp_name', $value)) {
                    $normalized[$key] = self::createUploadedFileFromSpec($value);
                    continue;
                } else {
                    $normalized[$key] = self::normalizeFiles($value);
                    continue;
                }
            } else {
                throw new InvalidArgumentException('Invalid value in files specification');
            }
        }
        return $normalized;
    }

    /**
     * @param array $server
     *
     * @return array
     */
    public static function marshalHeaders(array $server)
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (strpos($key, 'HTTP_COOKIE') === 0) {
                continue;
            }
            if ($value && 0 === strpos($key, 'HTTP_')) {
                $name = str_replace('_', ' ', substr($key, 5));
                $name = str_replace(' ', '-', ucwords(strtolower($name)));
                $name = strtolower($name);
                $headers[$name] = $value;
                continue;
            }
            if ($value && 0 === strpos($key, 'CONTENT_')) {
                $name = substr($key, 8);
                $name = 'Content-' . (('MD5' === $name) ? $name : ucfirst(strtolower($name)));
                $name = strtolower($name);
                $headers[$name] = $value;
                continue;
            }
        }
        return $headers;
    }

    /**
     * @param array $server
     * @param array $headers
     *
     * @return UriInterface|CUri
     * @throws InvalidArgumentException
     */
    public static function marshalUriFromServer(array $server, array $headers)
    {
        $uri = new CUri('');

        $scheme = 'http';
        $https = self::get('HTTPS', $server);
        if (($https && 'off' !== $https)
            || self::getHeader('x-forwarded-proto', $headers, false) === 'https'
        ) {
            $scheme = 'https';
        }
        if ('' !== $scheme) {
            $uri = $uri->withScheme($scheme);
        }

        $accumulator = (object)['host' => '', 'port' => null];
        self::marshalHostAndPortFromHeaders($accumulator, $server, $headers);
        $host = $accumulator->host;
        $port = $accumulator->port;
        if ('' !== $host) {
            /** @var UriInterface|CUri $uri */
            $uri = $uri->withHost($host);
            if ('' !== $port) {
                $uri = $uri->withPort($port);
            }
        }
        $path = self::marshalRequestUri($server);
        $path = self::stripQueryString($path);

        $query = '';
        if (array_key_exists('QUERY_STRING', $server)) {
            $query = ltrim($server['QUERY_STRING'], '?');
        }

        return $uri
            ->withPath($path)
            ->withQuery($query);
    }

    /**
     * @param stdClass $accumulator
     * @param array $server
     * @param array $headers
     */
    public static function marshalHostAndPortFromHeaders(stdClass $accumulator, array $server, array $headers)
    {
        if (self::getHeader('host', $headers, false)) {
            self::marshalHostAndPortFromHeader($accumulator, self::getHeader('host', $headers));
            return;
        }
        if (!array_key_exists('SERVER_NAME', $server)) {
            return;
        }
        $accumulator->host = $server['SERVER_NAME'];
        if (array_key_exists('SERVER_PORT', $server)) {
            $accumulator->port = (int)$server['SERVER_PORT'];
        }
        if (!array_key_exists('SERVER_ADDR', $server) || !preg_match('/^\[[0-9a-fA-F\:]+\]$/', $accumulator->host)) {
            return;
        }

        self::marshalIpv6HostAndPort($accumulator, $server);
    }

    /**
     * @param array $server
     *
     * @return string
     */
    public static function marshalRequestUri(array $server)
    {
        $iisUrlRewritten = self::get('IIS_WasUrlRewritten', $server);
        $unEncodedUrl = self::get('UNENCODED_URL', $server, '');
        if ('1' === $iisUrlRewritten && '' !== $unEncodedUrl) {
            return $unEncodedUrl;
        }
        $requestUri = self::get('REQUEST_URI', $server);

        $httpXRewriteUrl = self::get('HTTP_X_REWRITE_URL', $server);
        if ($httpXRewriteUrl !== null) {
            $requestUri = $httpXRewriteUrl;
        }

        $httpXOriginalUrl = self::get('HTTP_X_ORIGINAL_URL', $server);
        if ($httpXOriginalUrl !== null) {
            $requestUri = $httpXOriginalUrl;
        }
        if ($requestUri !== null) {
            return preg_replace('#^[^/:]+://[^/]+#', '', $requestUri);
        }
        $originalPathInfo = self::get('ORIG_PATH_INFO', $server);
        if (null === $originalPathInfo || '' === $originalPathInfo) {
            return '/';
        }
        return $originalPathInfo;
    }

    /**
     * @param mixed $path
     *
     * @return void|string
     */
    public static function stripQueryString($path)
    {
        if (($query_pos = strpos($path, '?')) !== false) {
            return substr($path, 0, $query_pos);
        }
        return $path;
    }

    /**
     * @param stdClass $accumulator
     * @param string|array $host
     *
     * @return void
     */
    private static function marshalHostAndPortFromHeader(stdClass $accumulator, $host)
    {
        if (is_array($host)) {
            $host = implode(', ', $host);
        }
        $accumulator->host = $host;
        $accumulator->port = null;

        if (preg_match('|\:(\d+)$|', $accumulator->host, $matches)) {
            $accumulator->host = substr($accumulator->host, 0, -1 * (strlen($matches[1]) + 1));
            $accumulator->port = (int)$matches[1];
        }

        if(null === $accumulator->port) {
            $accumulator->port = 80;
        }
    }

    /**
     * @param stdClass $accumulator
     * @param array $server
     */
    private static function marshalIpv6HostAndPort(stdClass $accumulator, array $server)
    {
        $accumulator->host = '[' . $server['SERVER_ADDR'] . ']';
        $accumulator->port = $accumulator->port ?: 80;
        if ($accumulator->port . ']' === substr($accumulator->host, strrpos($accumulator->host, ':') + 1)) {
            $accumulator->port = null;
        }
    }

    /**
     * @param array $value $_FILES
     *
     * @return array|UploadedFileInterface
     * @throws InvalidArgumentException
     */
    private static function createUploadedFileFromSpec(array $value)
    {
        if (is_array($value['tmp_name'])) {
            return self::normalizeNestedFileSpec($value);
        }
        return new CUploadedFile(
            $value['tmp_name'],
            $value['size'],
            $value['error'],
            $value['name'],
            $value['type']
        );
    }

    /**
     * @param array $files
     *
     * @return UploadedFileInterface[]
     * @throws InvalidArgumentException
     */
    private static function normalizeNestedFileSpec(array $files = [])
    {
        $normalizedFiles = [];
        $indexes = array_keys($files['tmp_name']);
        foreach ($indexes as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size' => $files['size'][$key],
                'error' => $files['error'][$key],
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
            ];
            $normalizedFiles[$key] = self::createUploadedFileFromSpec($spec);
        }
        return $normalizedFiles;
    }
}
