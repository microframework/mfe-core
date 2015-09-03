<?php namespace mfe\core\libs\components;

use mfe\core\libs\base\CComponent;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CDisplay
 *
 * @package mfe\core\libs\components
 */
class CDisplay extends CComponent
{
    const TYPE_DEBUG = 'debug';
    const TYPE_HTML5 = 'html5';
    const TYPE_JSON = 'json';

    /**
     * @param ResponseInterface $response
     * @param string $type
     */
    static public function display(ResponseInterface $response, $type = self::TYPE_HTML5)
    {
        print (string)$response->getBody();
    }
}
