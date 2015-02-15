<?php namespace mfe\core\core;

use mfe\core\libs\interfaces\IComponent;

use mfe\core\libs\components\CCore;
use mfe\core\mfe;

/**
 * Class Request
 * @package mfe\core\core
 */
class Request extends CCore implements IComponent
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

    static public function registerComponent()
    {
        mfe::registerComponent('request', [get_called_class(), 'initRequest']);
        return true;
    }
}
