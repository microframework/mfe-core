<?php namespace mfe\core\libs\http;

use InvalidArgumentException;
use mfe\core\libs\system\InputStream;
use mfe\core\libs\system\Stream;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class CRequest
 *
 * @package mfe\core\libs\http
 */
class CRequest implements ServerRequestInterface
{
    use TRequest;
    use TMessage;

    /** @var array */
    private $attributes = [];

    /** @var array */
    private $cookieParams = [];

    /** @var null|array|object */
    private $parsedBody;

    /** @var array */
    private $queryParams = [];

    /** @var array */
    private $serverParams;

    /** @var array */
    private $uploadedFiles;

    /**
     * @param array $serverParams
     * @param array $uploadedFiles
     * @param null|string $uri
     * @param null|string $method
     * @param string|resource|StreamInterface $body
     * @param array $headers
     * @throws InvalidArgumentException
     */
    public function __construct(
        array $serverParams = [],
        array $uploadedFiles = [],
        $uri = null,
        $method = null,
        $body = 'php://input',
        array $headers = []
    )
    {
        $this->validateUploadedFiles($uploadedFiles);
        $body = $this->getStream($body);
        $this->initialize($uri, $method, $body, $headers);
        $this->serverParams = $serverParams;
        $this->uploadedFiles = $uploadedFiles;
    }

    /**
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * @param array $uploadedFiles
     *
     * @return CRequest
     * @throws InvalidArgumentException
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $this->validateUploadedFiles($uploadedFiles);
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        return $new;
    }

    /**
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * @param array $cookies
     *
     * @return CRequest
     */
    public function withCookieParams(array $cookies)
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        return $new;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @param array $query
     *
     * @return CRequest
     */
    public function withQueryParams(array $query)
    {
        $new = clone $this;
        $new->queryParams = $query;
        return $new;
    }

    /**
     * @return array|null|object
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @param array|null|object $data
     *
     * @return CRequest
     */
    public function withParsedBody($data)
    {
        $new = clone $this;
        $new->parsedBody = $data;
        return $new;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $attribute
     * @param null $default
     *
     * @return null
     */
    public function getAttribute($attribute, $default = null)
    {
        if (!array_key_exists($attribute, $this->attributes)) {
            return $default;
        }
        return $this->attributes[$attribute];
    }

    /**
     * @param string $attribute
     * @param mixed $value
     *
     * @return CRequest
     */
    public function withAttribute($attribute, $value)
    {
        $new = clone $this;
        $new->attributes[$attribute] = $value;
        return $new;
    }

    /**
     * @param string $attribute
     *
     * @return CRequest
     */
    public function withoutAttribute($attribute)
    {
        if (!array_key_exists($attribute, $this->attributes)) {
            return clone $this;
        }
        $new = clone $this;
        unset($new->attributes[$attribute]);
        return $new;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        if ('' === $this->method) {
            return 'GET';
        }
        return $this->method;
    }

    /**
     * @param string $method
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
     * @param string|resource|StreamInterface $stream
     *
     * @return string|StreamInterface
     * @throws InvalidArgumentException
     */
    private function getStream($stream)
    {
        if ($stream === 'php://input') {
            return new InputStream();
        }
        if (!is_string($stream) && !is_resource($stream) && !$stream instanceof StreamInterface) {
            throw new InvalidArgumentException(
                'Stream must be a string stream resource identifier, '
                . 'an actual stream resource, '
                . 'or a Psr\Http\Message\StreamInterface implementation'
            );
        }
        if (!$stream instanceof StreamInterface) {
            return new Stream($stream, 'r');
        }
        return $stream;
    }

    /**
     * @param array $uploadedFiles
     *
     * @throws InvalidArgumentException
     */
    private function validateUploadedFiles(array $uploadedFiles)
    {
        foreach ($uploadedFiles as $file) {
            if (is_array($file)) {
                $this->validateUploadedFiles($file);
                continue;
            }
            if (!$file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Invalid leaf in uploaded files structure');
            }
        }
    }
}
