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
        $this->setStream($stream, $mode);
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
     * {@inheritdoc}
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
     */
    public function attach($resource, $mode = 'r')
    {
        $this->setStream($resource, $mode);
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
        $mode = $meta['mode'];
        return (
            strpos($mode, 'x')
            || strpos($mode, 'w')
            || strpos($mode, 'c')
            || strpos($mode, 'a')
            || strpos($mode, '+')
        );
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
        if (!$this->isWritable()) {
            throw new RuntimeException('Stream is not writable');
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
            throw new RuntimeException('Stream is not readable');
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

    /**
     * @param string|resource $stream
     * @param string $mode
     *
     * @throws InvalidArgumentException
     */
    private function setStream($stream, $mode = 'r')
    {
        $error = null;
        $resource = $stream;
        if (is_string($stream)) {
            set_error_handler(function ($e) use (&$error) {
                $error = $e;
            }, E_WARNING);
            $resource = fopen($stream, $mode);
            restore_error_handler();
        }
        if ($error) {
            throw new InvalidArgumentException('Invalid stream reference provided');
        }
        if (!is_resource($resource) || 'stream' !== get_resource_type($resource)) {
            throw new InvalidArgumentException(
                'Invalid stream provided; must be a string stream identifier or stream resource'
            );
        }
        if ($stream !== $resource) {
            $this->stream = $stream;
        }
        $this->resource = $resource;
    }
}
