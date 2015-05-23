<?php namespace mfe\core\libs\http;

/**
 * Class CRequest
 * @package mfe\core\libs\http
 */
class CRequest
{
    public $requestMethod;

    protected $global = [
        'GET',
        'POST',
    ];

    protected $variables = [
        'HEAD' => [],
        'OPTIONS' => [],
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
        'AJAX' => []
    ];

    public function __construct()
    {
        foreach($this->global as $key) {
            $this->variables[$key] = $GLOBALS["_{$key}"];
        }
    }

    public function isGet($key)
    {
    }

    public function isPost($key)
    {
    }

    public function isPut($key)
    {
    }

    public function isDelete($key)
    {
    }

    public function isHead($key)
    {
    }

    public function isOptions($key)
    {
    }

    public function isAjax($key)
    {
    }

    public function get($key)
    {
    }

    public function getPost($key)
    {
    }

    public function getPut($key)
    {
    }

    public function getDelete($key)
    {
    }

    public function getHead($key)
    {
    }

    public function getOptions($key)
    {
    }

    public function getAjax($key)
    {
    }
}
