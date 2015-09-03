<?php namespace mfe\core\libs\system;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class Stream
 *
 * @package mfe\core\libs\system
 */
class Stream implements StreamInterface
{
    /** @var resource */
    protected $resource;
    /** @var string|resource */
    protected $stream;

    /**
     * @param string|resource $stream
     * @param string $mode
     *
     * @throws InvalidArgumentException
     */
    public function __construct($stream, $mode = 'r')
    {
        $this->stream = $stream;
        if (is_resource($stream)) {
            $this->resource = $stream;
        } elseif (is_string($stream)) {
            set_error_handler(function () {
                throw new InvalidArgumentException(
                    'Invalid file provided for stream; must be a valid path with valid permissions'
                );
            }, E_WARNING);
            $this->resource = fopen($stream, $mode);
            restore_error_handler();
        } else {
            throw new InvalidArgumentException(
                'Invalid stream provided; must be a string stream identifier or resource'
            );
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!$this->isReadable()) {
            return '';
        }
        try {
            $this->rewind();
            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    /**
     *
     */
    public function close()
    {
        if (!$this->resource) {
            return;
        }
        $resource = $this->detach();
        fclose($resource);
    }

    /**
     * @return resource
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    /**
     * @param string|resource $resource
     * @param string $mode
     *
     * @throws InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function attach($resource, $mode = 'r')
    {
        $error = null;
        if (!is_resource($resource) && is_string($resource)) {
            set_error_handler(function ($e) use (&$error) {
                $error = $e;
            }, E_WARNING);
            $resource = fopen($resource, $mode);
            restore_error_handler();
        }
        if ($error) {
            throw new InvalidArgumentException('Invalid stream reference provided');
        }
        if (!is_resource($resource)) {
            throw new InvalidArgumentException(
                'Invalid stream provided; must be a string stream identifier or resource'
            );
        }
        $this->resource = $resource;
    }

    /**
     * @return null
     */
    public function getSize()
    {
        if (null === $this->resource) {
            return null;
        }
        $stats = fstat($this->resource);
        return $stats['size'];
    }

    /**
     * @return int
     * @throws RuntimeException
     */
    public function tell()
    {
        if (!$this->resource) {
            throw new RuntimeException('No resource available; cannot tell position');
        }
        $result = ftell($this->resource);
        if (!is_int($result)) {
            throw new RuntimeException('Error occurred during tell operation');
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function eof()
    {
        if (!$this->resource) {
            return true;
        }
        return feof($this->resource);
    }

    /**
     * @return bool
     */
    public function isSeekable()
    {
        if (!$this->resource) {
            return false;
        }
        $meta = stream_get_meta_data($this->resource);
        return $meta['seekable'];
    }

    /**
     * @param int $offset
     * @param int $whence
     *
     * @return bool
     * @throws RuntimeException
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->resource) {
            throw new RuntimeException('No resource available; cannot seek position');
        }

        if (!$this->isSeekable()) {
            throw new RuntimeException('Stream is not seekable');
        }
        $result = fseek($this->resource, $offset, $whence);
        if (0 !== $result) {
            throw new RuntimeException('Error seeking within stream');
        }
        return true;
    }

    /**
     * @return bool
     * @throws RuntimeException
     */
    public function rewind()
    {
        return $this->seek(0);
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        if (!$this->resource) {
            return false;
        }
        $meta = stream_get_meta_data($this->resource);
        return is_writable($meta['uri']);
    }

    /**
     * @param string $string
     *
     * @return int
     * @throws RuntimeException
     */
    public function write($string)
    {
        if (!$this->resource) {
            throw new RuntimeException('No resource available; cannot write');
        }
        $result = fwrite($this->resource, $string);
        if (false === $result) {
            throw new RuntimeException('Error writing to stream');
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        if (!$this->resource) {
            return false;
        }
        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];
        return (strpos($mode, 'r') || strpos($mode, '+'));
    }

    /**
     * @param int $length
     *
     * @return string
     * @throws RuntimeException
     */
    public function read($length)
    {
        if (!$this->resource) {
            throw new RuntimeException('No resource available; cannot read');
        }
        if (!$this->isReadable()) {
            throw new RuntimeException('Stream is not readable');
        }
        $result = fread($this->resource, $length);
        if (false === $result) {
            throw new RuntimeException('Error reading stream');
        }
        return $result;
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function getContents()
    {
        if (!$this->isReadable()) {
            return '';
        }
        $result = stream_get_contents($this->resource);
        if (false === $result) {
            throw new RuntimeException('Error reading from stream');
        }
        return $result;
    }

    /**
     * @param null $key
     *
     * @return array|null
     */
    public function getMetadata($key = null)
    {
        if (null === $key) {
            return stream_get_meta_data($this->resource);
        }
        $metadata = stream_get_meta_data($this->resource);
        if (!array_key_exists($key, $metadata)) {
            return null;
        }
        return $metadata[$key];
    }
}
