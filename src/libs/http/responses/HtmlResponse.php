<?php namespace mfe\core\libs\http\responses;

use InvalidArgumentException;
use mfe\core\libs\http\CResponse;
use mfe\core\libs\http\TResponseContent;
use mfe\core\libs\system\Stream;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class HtmlResponse
 *
 * @package mfe\core\libs\http\responses
 */
class HtmlResponse extends CResponse
{
    use TResponseContent;

    /**
     * @param string|StreamInterface $html
     * @param int $status
     * @param array $headers
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function __construct($html, $status = 200, array $headers = [])
    {
        parent::__construct(
            $this->createBody($html),
            $status,
            $this->injectContentType('text/html', $headers)
        );
    }

    /**
     * @param string|StreamInterface $html
     *
     * @return StreamInterface
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function createBody($html)
    {
        if ($html instanceof StreamInterface) {
            return $html;
        }

        if (!is_string($html)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid content (%s) provided to %s',
                (is_object($html) ? get_class($html) : gettype($html)),
                __CLASS__
            ));
        }

        $body = new Stream('php://temp', 'wb+');
        $body->write($html);
        return $body;
    }
}
