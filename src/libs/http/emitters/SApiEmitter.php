<?php namespace mfe\core\libs\http\emitters;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Class SApiEmitter
 *
 * @package mfe\core\libs\http\emitters
 */
class SApiEmitter
{
    /**
     * @param ResponseInterface $response
     * @param null|int $maxBufferLevel
     *
     * @throws RuntimeException
     */
    public function __construct(ResponseInterface $response, $maxBufferLevel = null)
    {
        if (headers_sent()) {
            throw new RuntimeException('Unable to emit response; headers already sent');
        }
        $this->emitStatusLine($response);
        $this->emitHeaders($response);
        $this->emitBody($response, $maxBufferLevel);
    }

    /**
     * @param ResponseInterface $response
     */
    private function emitStatusLine(ResponseInterface $response)
    {
        $reasonPhrase = $response->getReasonPhrase();
        header(sprintf(
            'HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            ($reasonPhrase ? ' ' . $reasonPhrase : '')
        ));
    }

    /**
     * @param ResponseInterface $response
     */
    private function emitHeaders(ResponseInterface $response)
    {
        foreach ($response->getHeaders() as $header => $values) {
            $name = $this->filterHeader($header);
            $first = true;
            foreach ($values as $value) {
                header(sprintf(
                    '%s: %s',
                    $name,
                    $value
                ), $first);
                $first = false;
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @param $maxBufferLevel
     */
    private function emitBody(ResponseInterface $response, $maxBufferLevel)
    {
        if (null === $maxBufferLevel) {
            $maxBufferLevel = ob_get_level();
        }
        while (ob_get_level() > $maxBufferLevel) {
            ob_end_flush();
        }
        echo $response->getBody();
    }

    /**
     * @param string $header
     *
     * @return string
     */
    private function filterHeader($header)
    {
        $filtered = str_replace('-', ' ', $header);
        $filtered = ucwords($filtered);
        return str_replace(' ', '-', $filtered);
    }
}
