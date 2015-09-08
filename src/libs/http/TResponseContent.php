<?php namespace mfe\core\libs\http;

/**
 * Trait TResponseContent
 *
 * @package mfe\core\libs\http
 */
trait TResponseContent
{
    /**
     * @param string $contentType
     * @param array $headers
     *
     * @return array
     */
    private function injectContentType($contentType, array $headers)
    {
        $hasContentType = array_reduce(array_keys($headers), function ($carry, $item) {
            return $carry ?: (strtolower($item) === 'content-type');
        }, false);
        if (!$hasContentType) {
            $headers['content-type'] = [$contentType];
        }
        return $headers;
    }
}
