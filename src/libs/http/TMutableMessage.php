<?php namespace mfe\core\libs\http;

use InvalidArgumentException;
use mfe\core\libs\helpers\CHttpSecurityHelper as HeaderSecurity;
use Psr\Http\Message\StreamInterface;

/**
 * Class TMutableMessage
 *
 * @package mfe\core\libs\http
 */
trait TMutableMessage
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
     * @return self
     */
    public function withProtocolVersion($version)
    {
        $this->protocol = $version;
        return $this;
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
     * @return string[]
     */
    public function getHeader($header)
    {
        if (!$this->hasHeader($header)) {
            return [];
        }
        $header = $this->headerNames[strtolower($header)];
        $value = $this->headers[$header];
        $value = is_array($value) ? $value : [$value];
        return $value;
    }

    /**
     * @return string|null
     */
    public function getHeaderLine($header)
    {
        $value = $this->getHeader($header);
        if (empty($value)) {
            return null;
        }
        return implode(',', $value);
    }

    /**
     * @param string|string[] $value
     *
     * @return self
     * @throws \InvalidArgumentException
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
        $this->headerNames[$normalized] = $header;
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * @param string|string[] $value
     *
     * @return self
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
        $this->headers[$header] = array_merge($this->headers[$header], $value);
        return $this;
    }

    /**
     * @param $header
     *
     * @return TMessage
     */
    public function withoutHeader($header)
    {
        if (!$this->hasHeader($header)) {
            return clone $this;
        }
        $normalized = strtolower($header);
        $original = $this->headerNames[$normalized];

        unset($this->headers[$original], $this->headerNames[$normalized]);
        return $this;
    }

    /**
     * @return StreamInterface
     */
    public function getBody()
    {
        return $this->stream;
    }

    /**
     * @param StreamInterface $body
     *
     * @return self
     * @throws InvalidArgumentException
     */
    public function withBody(StreamInterface $body)
    {
        $this->stream = $body;
        return $this;
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
            }
            if (!is_array($value)) {
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
        array_walk($values, __NAMESPACE__ . '\HeaderSecurity::assertValid');
    }
}
