<?php namespace mfe\core\libs\http\responses;

use InvalidArgumentException;
use mfe\core\libs\http\CResponse;
use mfe\core\libs\http\TResponseContent;
use mfe\core\libs\system\Stream;
use RuntimeException;

/**
 * Class JsonResponse
 *
 * @package mfe\core\libs\http\responses
 */
class JsonResponse extends CResponse
{

    use TResponseContent;

    /**
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @param int $encodingOptions
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function __construct($data, $status = 200, array $headers = [], $encodingOptions = 15)
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($this->jsonEncode($data, $encodingOptions));
        $headers = $this->injectContentType('application/json', $headers);
        parent::__construct($body, $status, $headers);
    }

    /**
     * @param mixed $data
     * @param int $encodingOptions
     *
     * @return string
     * @throws InvalidArgumentException
     */
    private function jsonEncode($data, $encodingOptions)
    {
        if (is_resource($data)) {
            throw new InvalidArgumentException('Cannot JSON encode resources');
        }

        json_encode(null);
        $json = json_encode($data, $encodingOptions);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(sprintf(
                'Unable to encode data to JSON in %s: %s',
                __CLASS__,
                json_last_error_msg()
            ));
        }
        return $json;
    }
}
