<?php namespace mfe\core\libs\http;

use InvalidArgumentException;
use mfe\core\libs\helpers\CHttpSecurityHelper as HeaderSecurity;
use mfe\core\libs\helpers\CHttpSecurityHelper;
use mfe\core\libs\system\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class CResponse
 *
 * @package mfe\core\libs\http
 */
class CResponse implements ResponseInterface
{
    use TMessage;

    /** @var array */
    static private $phrases = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /** @var string */
    private $reasonPhrase = '';

    /** @var int */
    private $statusCode = 200;

    /**
     * @param string|resource|StreamInterface $body
     * @param int $status
     * @param array $headers
     * @throws InvalidArgumentException
     */
    public function __construct($body = 'php://memory', $status = 200, array $headers = [])
    {
        if (!is_string($body) && !is_resource($body) && !$body instanceof StreamInterface) {
            throw new InvalidArgumentException(
                'Stream must be a string stream resource identifier, '
                . 'an actual stream resource, '
                . 'or a Psr\Http\Message\StreamInterface implementation'
            );
        }
        if (null !== $status) {
            $this->validateStatus($status);
        }
        $this->stream = ($body instanceof StreamInterface) ? $body : new Stream($body, 'wb+');
        $this->statusCode = $status ? (int)$status : 200;
        list($this->headerNames, $headers) = $this->filterHeaders($headers);
        $this->assertHeaders($headers);
        $this->headers = $headers;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getReasonPhrase()
    {
        if (!$this->reasonPhrase
            && array_key_exists($this->statusCode, static::$phrases)
        ) {
            $this->reasonPhrase = static::$phrases[$this->statusCode];
        }
        return $this->reasonPhrase;
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $this->validateStatus($code);
        $new = clone $this;
        $new->statusCode = (int)$code;
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    /**
     * @param int|float|string $code
     *
     * @throws InvalidArgumentException
     */
    private function validateStatus($code)
    {
        if (!is_numeric($code)
            || is_float($code)
            || $code < 100
            || $code >= 600
        ) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status code "%s"; must be an integer between 100 and 599, inclusive',
                (is_scalar($code) ? $code : gettype($code))
            ));
        }
    }

    /**
     * @param array $headers
     *
     * @throws InvalidArgumentException
     */
    private function assertHeaders(array $headers)
    {
        foreach ($headers as $name => $headerValues) {
            HeaderSecurity::assertValidName($name);
            array_walk($headerValues, CHttpSecurityHelper::class . '\::assertValid');
        }
    }
}
