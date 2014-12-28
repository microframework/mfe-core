<?php namespace mfe;

/**
 * Class Page
 * @package mfe
 */
class Page extends CCore {
    static protected $instance;
    public $uid = null;

    public $_language = 'en_US';
    public $_dir = 'ltr';
    public $_charset = 'utf-8';
    public $_type = 'text\html';

    public $_title = 'Default Page';
    public $_icon = null;
    public $_viewport = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';

    public $_author = null;
    public $_keywords = null;
    public $_description = null;

    public $_content = null;

    protected $_auto_refresh = [false, 0];
    protected $_auto_redirect = [false, '/', 0];

    protected $meta = [];
    protected $styles = [];
    protected $scripts = [];

    public $data = [];

    protected $layout = null;
    protected $layout_extension = '.tpl';

    public function __toString() {
        return (string)$this->layout();
    }

    public function __get($value) {
        return ('_' == substr($value, 0, 1) && isset($this->{$value})) ?
            ($this->{$value}) :
            ((isset($this->data[$value])) ?
                $this->data[$value] : null);
    }

    public function __set($key, $value) {
        if ('_' == substr($key, 0, 1) && isset($this->{$key})) {
            $this->{$key} = $value;
            return true;
        }
        $this->data[$key] = $value;
        return true;
    }

    public function __construct($layout = null, $data = [], $uid = null) {
        if (!mfe::app()->loader->aliasDirectoryExist('@layout')) {
            mfe::app()->loader->registerAliasDirectory('@layout', 'assets/layouts');
        }

        if (!is_null($layout)) {
            $this->setLayout($layout);
        }

        if (!is_null($data)) $this->data = $data;
        if (!is_null($uid)) {
            $this->guid = 'page_' . $uid;
        } else {
            $this->guid = 'page_' . md5($this);
        }
    }

    public function render($data) {
        array_merge($this->data, $data);
        if (method_exists('\mfe\Display', 'display')) {
            call_user_func_array(['\mfe\Display', 'display'], (string)$this, $this->layout . '_' . $this->uid);
        }
        return (string)$this;
    }

    protected function parse($layout) {
        return preg_replace_callback(
            '#\{([a-z0-9\-_\#]*?)\}#Ssi',
            function ($match) {
                return $this->{substr($match[0], 1, -1)};
            }, $layout);
    }

    protected function layout() {
        return (!is_null($this->layout)) ?
            $this->parse($this->layout) : $this->parse($this->get_default_layout());
    }

    protected function get_default_layout() {
        return "<!DOCTYPE html>\r\n<html lang=\"{_language}\" dir=\"{_dir}\">
    <head>
        <meta charset='{_charset}'>
        <meta name='viewport' content='{_viewport}'>\r\n" .
        ((!is_null($this->_icon)) ? ("\r\n        <link rel='shortcut icon' href='{_icon}' type='image/x-icon'>") : '') .
        "\r\n        <title>{_title}</title>" .
        ((!is_null($this->_author)) ? ("\r\n        <meta name='author' content='{_author}'>") : '') .
        ((!is_null($this->_keywords)) ? ("\r\n        <meta name='keywords' content='{_keywords}'>") : '') .
        ((!is_null($this->_description)) ? ("\r\n        <meta name='description' content='{_description}'>") : '') .
        (!empty($this->meta) ? $this->generateMetaTags() . "\r\n" : '') .
        (!empty($this->styles) ? $this->generateStyles() . "\r\n" : '') .
        (!empty($this->scripts) ? $this->generateScripts() : '') .
        "\r\n    </head>
    <body>
        {_content}
    </body>\r\n</html>\r\n";
    }

    public function addMeta($key, $content) {
        $this->meta[$key] = $content;
        return $this;
    }

    public function addStyles($key, $src, $type = 'application/javascript', $media = 'screen', $rel = 'stylesheet') {
        $this->styles[$key] = [$src, $type, $media, $rel];
        return $this;
    }

    public function addScripts($key, $src, $type = 'application/javascript') {
        $this->scripts[$key] = [$src, $type];
        return $this;
    }

    protected function generateMetaTags() {
        $html = '';
        foreach ($this->meta as $key => $content) {
            $html .= "\r\n        <meta name='$key' content='$content' />";
        }
        return $html;
    }

    protected function generateStyles() {
        $html = '';
        foreach ($this->styles as $key => $i) {
            $html .= "\r\n        <link rel='{$i[3]}' media='{$i[2]}' type='{$i[1]}' href='{$i[0]}' />";
        }
        return $html;
    }

    protected function generateScripts() {
        $html = '';
        foreach ($this->scripts as $key => $i) {
            $html .= "\r\n        <script type='{$i[1]}' src='{$i[0]}'></script>";
        }
        return $html;
    }

    static public function getInstance($layout = null, $data = [], $uid = null) {
        if(is_null(self::$instance)){
            $class = get_called_class();
            self::$instance = new $class($layout, $data, $uid);
        }
        return self::$instance;
    }

    public function setLayout($layout) {
        if(!($this->layout = mfe::loadFile('@engine.@layout.' . $layout, $this->layout_extension))) {
            throw new CException('Not found layout file: ' . $layout . $this->layout_extension . ' in directories: '
                . PHP_EOL . implode('; ' . PHP_EOL,  mfe::getInstance()->loader->getRealPaths('@engine.@layout.', true)));
        }
        return $this;
    }
}

mfe::registerComponent('page', ['mfe\Page', 'getInstance']);
