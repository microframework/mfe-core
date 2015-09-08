<?php namespace libs\http\responses;

use InvalidArgumentException;
use mfe\core\libs\http\CResponse;
use mfe\core\libs\system\Stream;

/**
 * Class EmptyResponse
 *
 * @package libs\http\responses
 */
class EmptyResponse extends CResponse
{
    /**
     * @param int $status
     * @param array $headers
     *
     * @throws InvalidArgumentException
     */
    public function __construct($status = 204, array $headers = [])
    {
        $body = new Stream('php://temp', 'r');
        parent::__construct($body, $status, $headers);
    }

    /**
     * @param array $headers
     * @return self
     */
    public static function withHeaders(array $headers)
    {
        return new static(204, $headers);
    }
}
