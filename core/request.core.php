<?php namespace mfe;
/**
 * Class Request
 * @package mfe
 */

class RequestCore implements ImfeComponent {
    const CORE_COMPONENT_NAME = 'RequestCore';
    const CORE_COMPONENT_VERSION = '1.0.0';

    public $is_ajax = false;

    public function initRequest() {
        global $_SERVER;
    }

    public function initProtocol_HTTP() {

    }

    public function initProtocol_HTTPS() {

    }

    public function initProtocol_CLI() {

    }

    public function initProtocol_AJAX() {

    }

    static public function registerComponent() {
    }
}

mfe::registerComponent('request', [get_called_class(), 'initRequest']);