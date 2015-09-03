<?php namespace mfe\core\libs\system;

use RuntimeException;
use InvalidArgumentException;

/**
 * Class InputStream
 *
 * @package mfe\core\libs\system
 */
class InputStream extends Stream
{
    /** @var string */
    private $cache = '';

    /** @var bool */
    private $reachedEof = false;

    /**
     * @param  string|resource $stream
     * @param  string $mode
     *
     * @throws InvalidArgumentException
     */
    public function __construct($stream = 'php://input', $mode = 'r')
    {
        $mode = 'r';
        parent::__construct($stream, $mode);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->reachedEof) {
            return $this->cache;
        }
        $this->getContents();
        return $this->cache;
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * @param int $length
     *
     * @return string
     * @throws RuntimeException
     */
    public function read($length)
    {
        $content = parent::read($length);
        if ($content && !$this->reachedEof) {
            $this->cache .= $content;
        }
        if ($this->eof()) {
            $this->reachedEof = true;
        }
        return $content;
    }

    /**
     * @param int $maxLength
     *
     * @return string
     */
    public function getContents($maxLength = -1)
    {
        if ($this->reachedEof) {
            return $this->cache;
        }
        $contents = stream_get_contents($this->resource, $maxLength);
        $this->cache .= $contents;
        if ($maxLength === -1 || $this->eof()) {
            $this->reachedEof = true;
        }
        return $contents;
    }
}
