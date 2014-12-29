<?php namespace mfe;

/**
 * Class CLayout
 *
 * @package mfe
 */
class CLayout extends CComponent {
    protected $layout;
    protected $layout_extension = '.php';

    protected $data;

    protected $result;

    /**
     * @param bool $layout
     * @param array $data
     */
    public function __construct($layout = false, $data = []) {
        if (!mfe::getInstance()->loader->aliasDirectoryExist('@layout'))
            mfe::getInstance()->loader->registerAliasDirectory('@layout', 'assets/layouts');

        if (!is_null($layout)) {
            $this->setLayout($layout);
        }

        if(is_array($data) && !empty($data)) $this->data = $data;
    }

    /**
     * @return string
     */
    public function __toString() {
        return (string) ($this->result) ? $this->result : $this->render();
    }

    /**
     * @param $value
     * @return null
     */
    public function __get($value) {
        return (isset($this->data[$value])) ? $this->data[$value] : null;
    }

    /**
     * @param $value
     * @return bool
     */
    public function __isset($value) {
        return (isset($this->data[$value])) ? true : false;
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function __set($key, $value) {
        $this->data[$key] = $value;
        return true;
    }

    /**
     * @param $layout
     * @return $this
     */
    public function setLayout($layout) {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @param $data
     */
    public function setData($data) {
        if(is_array($data) && !empty($data)) $this->data = $data;
    }

    /**
     * @param $layout
     * @return mixed
     */
    protected function parse($layout) {
        return preg_replace_callback(
            '#\{([a-z0-9\-_\#]*?)\}#Ssi',
            function ($match) {
                return $this->{substr($match[0], 1, -1)};
            }, $layout);
    }

    /**
     * @return bool
     */
    protected function loadLayout(){
        $layouts = mfe::app()->loader->getRealPaths('@engine.@layout.' . $this->layout);

        foreach($layouts as $layout){
            /** @noinspection PhpIncludeInspection */
            if($result = @include($layout . $this->layout_extension)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function render() {
        if(!($this->layout)){
            CLog::error('[' . __CLASS__ . '] Layout file not selected!');
            mfe::stop(0x00000E3);
        }

        ob_start(function($layout) {
            return preg_replace_callback(
                '#\{([a-z0-9\-_\#]*?)\}#Ssi',
                function ($match) {
                    return isset($this->{substr($match[0], 1, -1)}) ? $this->{substr($match[0], 1, -1)} : $match[0];
                }, $layout);
        });

        if(!$this->loadLayout()) {
            CLog::error('[' . __CLASS__ . '] Not found layout file: ' . $this->layout . $this->layout_extension . ' in directories: '
                . PHP_EOL . implode('; ' . PHP_EOL,  mfe::app()->loader->getRealPaths('@engine.@layout.', true)));
            mfe::stop(0x00000E3);
        }
        $this->result = ob_get_contents();
        ob_clean();

        return $this->result;
    }
}