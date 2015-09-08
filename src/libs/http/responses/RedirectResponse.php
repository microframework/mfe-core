<?php namespace mfe\core\libs\http\responses;

use InvalidArgumentException;
use mfe\core\libs\http\CResponse;
use Psr\Http\Message\UriInterface;

/**
 * Class RedirectResponse
 *
 * @package mfe\core\libs\http\responses
 */
class RedirectResponse extends CResponse
{
    /**
     * @param string|UriInterface $uri
     * @param int $status
     * @param array $headers
     *
     * @throws InvalidArgumentException
     */
    public function __construct($uri, $status = 302, array $headers = [])
    {
        if (!is_string($uri) && !$uri instanceof UriInterface) {
            throw new InvalidArgumentException(sprintf(
                'Uri provided to %s MUST be a string or Psr\Http\Message\UriInterface instance; received "%s"',
                __CLASS__,
                (is_object($uri) ? get_class($uri) : gettype($uri))
            ));
        }
        $headers['location'] = [(string)$uri];
        parent::__construct('php://temp', $status, $headers);
    }
}
