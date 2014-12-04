<?php namespace mfe;

/**
 * Class Page
 * @package mfe
 */
class Page {
    public $guid = null;
    public $layout = null;

    public $_language = 'en_US';
    public $_charset = 'utf-8';
    public $_type = 'text\html';

    public $_title = 'Default Page';
    public $_icon = null;
    public $_viewport = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';

    public $_author = null;
    public $_keywords = null;
    public $_descriptions = null;

    public $_auto_refresh = [false, 0];
    public $_auto_redirect = [false, '/', 0];

    public $meta = [];
    public $styles = [];
    public $scripts = [];

    public $data = [];

    public function __toString() {
        return (string) $this->layout();
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

    public function __construct($layout = null, $data = [], $guid = null) {
        $this->guid = 'page_' . md5($this);
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

    private function get_default_layout() {
        return "<!DOCTYPE html>\r\n<html lang=\"{_language}\">
    <head>
        <meta charset='{_charset}'>
        <meta name='viewport' content='{_viewport}'>
        <title>{_title}</title>\r\n" .
((!is_null($this->_author)) ? ("        <meta name='author' content='{_author}'>\r\n") : '') .
((!is_null($this->_keywords)) ? ("        <meta name='keywords' content='{_keywords}'>\r\n") : '') .
((!is_null($this->_description)) ? ("        <meta name='description' content='{_description}'>\r\n") : '') .
((!is_null($this->_icon)) ? ("        <link rel='shortcut icon' href='{_icon}' type='image/x-icon'>") : '') .
        (!empty($this->meta) ? $this->generateMetaTags() : '').
        (!empty($this->styles) ? $this->generateStylesTypes() : '').
        (!empty($this->scripts) ? $this->generateScriptsTags() : '');
    "</head>
    <body>
        {_content}
    </body>\r\n</html>\r\n";
    }

    public function addMeta($key,$content) {
        $this->meta[$key]=$content;
        return $this;
    }

    private function generateMetaTags() {
        $html='';
        foreach ($this->meta as $key => $content) {
            $html.= "\r\n\t\t<meta name='$key' content='$content'>\r";
        }
        return $html;
    }

    public function addStyles($key,$types) {
        $this->styles[$key]=$types;
        return $this;
    }

    private function generateStylesTypes() {
        $html='';
        foreach ($this->styles as $key => $types) {
            $html.= "\r\n\t\t<style type='$types'></style>\r";
        }
        return $html;
    }

    public function addScripts($key,$src) {
        $this->scripts[$key]=$src;
        return $this;
    }

    private function generateScriptsTags() {
        $html='';
        foreach ($this->scripts as $key => $src) {
            $html.= "\r\n\t\t<script type='$key' src='//$src'></script>\r";
        }
        return $html;
    }
/*
    public function add_styles_string() {

    }

    public function add_scripts_string() {

    }
*/
}

$page = new Page('layout.alias', $data = []);

$page->_author = 'DeVinterX';
$page->_keywords = 'key,word';
$page->_description = 'Description';
$page->_icon = 'favicon.ico';
$page->addMeta("Dex","PexMex");
$page->addStyles("OnePage","text/css");
$page->addScripts("OnePage","js/one.js");
header("Content-type: text/html; charset=utf-8");
header("X-Powered-By: Bubu");
print $page;
