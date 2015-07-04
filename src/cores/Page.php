<?php namespace mfe\core\cores;

use mfe\core\libs\components\CException;
use mfe\core\libs\interfaces\IComponent;

use mfe\core\libs\base\CCore;
use mfe\core\mfe;

/**
 * Class Page
 *
 * @deprecated
 * @package mfe\core\cores
 */
class Page extends CCore
{
    const COMPONENT_NAME = 'Page';
    const COMPONENT_VERSION = '1.0.0';

    /** @var Page */
    static public $instance;

    public $uid;

    public $_language = 'en_US';
    public $_dir = 'ltr';
    public $_charset = 'utf-8';
    public $_type = 'text\html';

    public $_title = 'Default Page';
    public $_icon;
    public $_viewport = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';

    public $_author;
    public $_keywords;
    public $_description;

    public $_content;

    protected $_auto_refresh = [false, 0];
    protected $_auto_redirect = [false, '/', 0];

    protected $meta = [];
    protected $styles = [];
    protected $scripts = [];

    public $data = [];

    protected $layout;
    protected $layout_extension = '.tpl';

    public function __toString()
    {
        return (string)$this->layout();
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function __get($key)
    {
        try {
            parent::__get($key);
        } catch (CException $e) {
        }

        return ('_' === substr($key, 0, 1) && isset($this->{$value})) ?
            ($this->{$key}) :
            ((array_key_exists($key, $this->data)) ? $this->data[$key] : null);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return bool|mixed
     */
    public function __set($key, $value)
    {
        try {
            parent::__set($key, $value);
        } catch (CException $e) {
        }

        if (isset($this->{$key}) && '_' === substr($key, 0, 1)) {
            $this->{$key} = $value;
            return true;
        }

        $this->data[$key] = $value;

        return true;
    }

    public function __construct($layout = null, array $data = [], $uid = null)
    {
//        if (!MfE::app()->loader->aliasDirectoryExist('@layout')) {
//            MfE::app()->loader->registerAliasDirectory('@layout', 'assets/layouts');
//        }

        if (null !== $layout) {
            $this->setLayout($layout);
        }

        if (null !== $data) {
            $this->data = $data;
        }
        if (null !== $uid) {
            $this->uid = 'page_' . $uid;
        } else {
            $this->uid = 'page_' . md5($this);
        }
    }

    public function render(array $data = [])
    {
        array_merge($this->data, $data);
        if (method_exists('\mfe\Display', 'display')) {
            call_user_func_array(['\mfe\Display', 'display'], (string)$this, $this->layout . '_' . $this->uid);
        }
        return (string)$this;
    }

    protected function parse($layout)
    {
        return preg_replace_callback(
            '#\{([a-z0-9\-_\#]*?)\}#Si',
            function ($match) {
                return $this->{substr($match[0], 1, -1)};
            }, $layout);
    }

    protected function layout()
    {
        return (null !== $this->layout) ?
            $this->parse($this->layout) : $this->parse($this->get_default_layout());
    }

    protected function get_default_layout()
    {
        return "<!DOCTYPE html>\r\n<html lang=\"{_language}\" dir=\"{_dir}\">
    <head>
        <meta charset='{_charset}'>
        <meta name='viewport' content='{_viewport}'>\r\n" .
        ((null !== $this->_icon) ? ("\r\n        <link rel='shortcut icon' href='{_icon}' type='image/x-icon'>") : '') .
        "\r\n        <title>{_title}</title>" .
        ((null !== $this->_author) ? ("\r\n        <meta name='author' content='{_author}'>") : '') .
        ((null !== $this->_keywords) ? ("\r\n        <meta name='keywords' content='{_keywords}'>") : '') .
        ((null !== $this->_description) ? ("\r\n        <meta name='description' content='{_description}'>") : '') .
        (([] !== $this->meta) ? $this->generateMetaTags() . "\r\n" : '') .
        (([] !== $this->styles) ? $this->generateStyles() . "\r\n" : '') .
        (([] !== $this->scripts) ? $this->generateScripts() : '') .
        "\r\n    </head>
    <body>
        {_content}
    </body>\r\n</html>\r\n";
    }

    public function addMeta($key, $content)
    {
        $this->meta[$key] = $content;
        return $this;
    }

    public function addStyles($key, $src, $type = 'application/javascript', $media = 'screen', $rel = 'stylesheet')
    {
        $this->styles[$key] = [$src, $type, $media, $rel];
        return $this;
    }

    public function addScripts($key, $src, $type = 'application/javascript')
    {
        $this->scripts[$key] = [$src, $type];
        return $this;
    }

    protected function generateMetaTags()
    {
        $html = '';
        foreach ($this->meta as $key => $content) {
            $html .= "\r\n        <meta name='$key' content='$content' />";
        }
        return $html;
    }

    protected function generateStyles()
    {
        $html = '';
        foreach ($this->styles as $key => $i) {
            $html .= "\r\n        <link rel='{$i[3]}' media='{$i[2]}' type='{$i[1]}' href='{$i[0]}' />";
        }
        return $html;
    }

    protected function generateScripts()
    {
        $html = '';
        foreach ($this->scripts as $key => $i) {
            $html .= "\r\n        <script type='{$i[1]}' src='{$i[0]}'></script>";
        }
        return $html;
    }

    public function setLayout($layout)
    {
        if (!($this->layout = MfE::loadFile('@engine.@layout.' . $layout, $this->layout_extension))) {
            throw new CException('Not found layout file: ' . $layout . $this->layout_extension . ' in directories: '
                . PHP_EOL . implode('; ' . PHP_EOL, MfE::app()->loader->getRealPaths('@engine.@layout.', true)));
        }
        return $this;
    }

    public function setLayoutExtension($extension)
    {
        if ('.' === substr($extension, 0, 1)) {
            $this->layout_extension = $extension;
        }
        return $this;
    }

    public function addData(array $data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }
}
