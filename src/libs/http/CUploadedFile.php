<?php namespace mfe\core\libs\http;

use InvalidArgumentException;
use mfe\core\libs\system\Stream;
use RuntimeException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class UploadedFile
 *
 * @package mfe\core\libs\http
 */
class CUploadedFile implements UploadedFileInterface
{
    /** @var string */
    private $clientFilename;

    /** @var string */
    private $clientMediaType;

    /** @var int */
    private $error;

    /** @var null|string */
    private $file;

    /** @var bool */
    private $moved = false;

    /** @var int */
    private $size;

    /** @var null|StreamInterface */
    private $stream;

    /**
     * @param string|resource|Stream $streamOrFile
     * @param int $size
     * @param int $errorStatus
     * @param null|string $clientFilename
     * @param null|string $clientMediaType
     *
     * @throws InvalidArgumentException
     */
    public function __construct($streamOrFile, $size, $errorStatus, $clientFilename = null, $clientMediaType = null)
    {
        if ($errorStatus === UPLOAD_ERR_OK) {
            if (is_string($streamOrFile)) {
                $this->file = $streamOrFile;
            }
            if (is_resource($streamOrFile)) {
                $this->stream = new Stream($streamOrFile);
            }
            if (!$this->file && !$this->stream) {
                if (!$streamOrFile instanceof StreamInterface) {
                    throw new InvalidArgumentException('Invalid stream or file provided for UploadedFile');
                }
                $this->stream = $streamOrFile;
            }
        }
        if (!is_int($size)) {
            throw new InvalidArgumentException('Invalid size provided for UploadedFile; must be an int');
        }
        $this->size = $size;
        if (!is_int($errorStatus)
            || 0 > $errorStatus
            || 8 < $errorStatus
        ) {
            throw new InvalidArgumentException(
                'Invalid error status for UploadedFile; must be an UPLOAD_ERR_* constant'
            );
        }
        $this->error = $errorStatus;
        if (null !== $clientFilename && !is_string($clientFilename)) {
            throw new InvalidArgumentException(
                'Invalid client filename provided for UploadedFile; must be null or a string'
            );
        }
        $this->clientFilename = $clientFilename;
        if (null !== $clientMediaType && !is_string($clientMediaType)) {
            throw new InvalidArgumentException(
                'Invalid client media type provided for UploadedFile; must be null or a string'
            );
        }
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * @return Stream|null|StreamInterface
     * @throws RuntimeException
     */
    public function getStream()
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }
        if ($this->moved) {
            throw new RuntimeException('Cannot retrieve stream after it has already been moved');
        }
        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }
        $this->stream = new Stream($this->file);
        return $this->stream;
    }

    /**
     * @param string $targetPath
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function moveTo($targetPath)
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }
        if (!is_string($targetPath)) {
            throw new InvalidArgumentException(
                'Invalid path provided for move operation; must be a string'
            );
        }
        if ('' === $targetPath) {
            throw new InvalidArgumentException(
                'Invalid path provided for move operation; must be a non-empty string'
            );
        }
        if ($this->moved) {
            throw new RuntimeException('Cannot move file; already moved!');
        }
        $sapi = PHP_SAPI;
        if ((null !== $sapi && '' !== $sapi) || !$this->file || 0 === strpos($sapi, 'cli')) {
            $this->writeFile($targetPath);
        } else {
            if (false === move_uploaded_file($this->file, $targetPath)) {
                throw new RuntimeException('Error occurred while moving uploaded file');
            }
        }
        $this->moved = true;
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string|null
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * @return string
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    /**
     * @param string $path
     *
     * @throws RuntimeException
     */
    private function writeFile($path)
    {
        $handle = fopen($path, 'wb+');
        if (false === $handle) {
            throw new RuntimeException('Unable to write to designated path');
        }
        $stream = $this->getStream();
        $stream->rewind();
        while (!$stream->eof()) {
            fwrite($handle, $stream->read(4096));
        }
        fclose($handle);
    }
}
