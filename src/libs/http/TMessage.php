<?php namespace mfe\core\libs\http;

use InvalidArgumentException;
use mfe\core\libs\helpers\CHttpSecurityHelper as HeaderSecurity;
use mfe\core\libs\helpers\CHttpSecurityHelper;
use Psr\Http\Message\StreamInterface;

/**
 * Class TMessage
 *
 * @package mfe\core\libs\http
 */
trait TMessage
{
    /** @var array */
    protected $headers = [];

    /** @var array */
    protected $headerNames = [];

    /** @var string */
    private $protocol = '1.1';

    /** @var StreamInterface */
    private $stream;

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * @param string $version
     *
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $new = clone $this;
        $new->protocol = $version;
        return $new;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $header
     *
     * @return bool
     */
    public function hasHeader($header)
    {
        return array_key_exists(strtolower($header), $this->headerNames);
    }

    /**
     * @param string $header
     * @return string[]
     */
    public function getHeader($header)
    {
        if (!$this->hasHeader($header)) {
            return [];
        }
        $header = $this->headerNames[strtolower($header)];
        $value = $this->headers[$header];
        return (array)$value;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getHeaderLine($name)
    {
        $value = $this->getHeader($name);
        if (null === $value) {
            return '';
        }
        return implode(',', $value);
    }

    /**
     * @param string $header
     * @param string|string[] $value
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function withHeader($header, $value)
    {
        if (is_string($value)) {
            $value = [$value];
        }
        if (!is_array($value) || !$this->arrayContainsOnlyStrings($value)) {
            throw new InvalidArgumentException(
                'Invalid header value; must be a string or array of strings'
            );
        }
        HeaderSecurity::assertValidName($header);
        self::assertValidHeaderValue($value);
        $normalized = strtolower($header);
        $new = clone $this;
        $new->headerNames[$normalized] = $header;
        $new->headers[$header] = $value;
        return $new;
    }

    /**
     * @param string $header
     * @param string|string[] $value
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public function withAddedHeader($header, $value)
    {
        if (is_string($value)) {
            $value = [$value];
        }
        if (!is_array($value) || !$this->arrayContainsOnlyStrings($value)) {
            throw new InvalidArgumentException(
                'Invalid header value; must be a string or array of strings'
            );
        }
        HeaderSecurity::assertValidName($header);
        self::assertValidHeaderValue($value);
        if (!$this->hasHeader($header)) {
            return $this->withHeader($header, $value);
        }
        $normalized = strtolower($header);
        $header = $this->headerNames[$normalized];
        $new = clone $this;
        $new->headers[$header] = array_merge($this->headers[$header], $value);
        return $new;
    }

    /**
     * @param string $header
     *
     * @return static
     */
    public function withoutHeader($header)
    {
        if (!$this->hasHeader($header)) {
            return clone $this;
        }
        $normalized = strtolower($header);
        $original = $this->headerNames[$normalized];
        $new = clone $this;
        unset($new->headers[$original], $new->headerNames[$normalized]);
        return $new;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->stream;
    }

    /**
     * @param StreamInterface $body
     *
     * @return static
     * @throws InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        $new = clone $this;
        $new->stream = $body;
        return $new;
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    private function arrayContainsOnlyStrings(array $array)
    {
        return array_reduce($array, [__CLASS__, 'filterStringValue'], true);
    }

    /**
     * @param array $originalHeaders
     *
     * @return array
     */
    private function filterHeaders(array $originalHeaders)
    {
        $headerNames = $headers = [];
        foreach ($originalHeaders as $header => $value) {
            if (!is_string($header)) {
                continue;
            }
            if (!is_array($value) && !is_string($value)) {
                continue;
            } elseif (!is_array($value)) {
                $value = [$value];
            }
            $headerNames[strtolower($header)] = $header;
            $headers[$header] = $value;
        }
        return [$headerNames, $headers];
    }

    /**
     * @param bool $carry
     * @param mixed $item
     *
     * @return bool
     */
    private static function filterStringValue($carry, $item)
    {
        if (!is_string($item)) {
            return false;
        }
        return $carry;
    }

    /**
     * @param string[] $values
     *
     * @throws InvalidArgumentException
     */
    private static function assertValidHeaderValue(array $values)
    {
        array_walk($values, CHttpSecurityHelper::class . '::assertValid');
    }
}
