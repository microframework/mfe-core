<?php namespace mfe\core\cores;

use mfe\core\libs\base\CCore;

/**
 * Class Request
 *
 * @deprecated
 * @package mfe\core\cores
 */
class Request extends CCore
{
    const COMPONENT_NAME = 'Request';
    const COMPONENT_VERSION = '1.0.0';

    public $is_ajax = false;

    public function initRequest()
    {
        global $_SERVER;
    }

    public function initProtocol_HTTP()
    {

    }

    public function initProtocol_HTTPS()
    {

    }

    public function initProtocol_CLI()
    {

    }

    public function initProtocol_AJAX()
    {

    }
}
