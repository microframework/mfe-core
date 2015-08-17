<?php namespace mfe\core\libs\components;

use mfe\core\libs\base\CComponent;
use mfe\core\mfe;

/**
 * Class CLayout
 *
 * @deprecated
 * @package mfe\core\libs\components
 */
class CLayout extends CComponent
{
    protected $layout;
    protected $layout_extension = '.php';

    protected $data;

    protected $result;

    /**
     * @param bool $layout
     * @param array $data
     */
    public function __construct($layout = false, array $data = [])
    {
        if (null !== $layout) {
            $this->setLayout($layout);
        }

        if (is_array($data) && [] !== $data) {
            $this->data = $data;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->result;
    }

    /**
     * @param $value
     *
     * @return null
     */
    public function __get($value)
    {
        return (array_key_exists($value, $this->data)) ? $this->data[$value] : null;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function __isset($value)
    {
        return (array_key_exists($value, $this->data)) ? true : false;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
        return true;
    }

    /**
     * @param $layout
     *
     * @return $this
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @param $data
     */
    public function setData($data)
    {
        if (is_array($data) && [] !== $data) {
            $this->data = $data;
        }
    }

    /**
     * @param $layout
     *
     * @return mixed
     */
    protected function parse($layout)
    {
        return preg_replace_callback(
            '#\{([a-z0-9\-_\#]*?)\}#Si',
            function ($match) {
                return $this->{substr($match[0], 1, -1)};
            }, $layout);
    }

    /**
     * TODO:: загрузка через @loader
     * @return bool
     */
    protected function loadLayout()
    {
        $layout = MfE::ENGINE_DIR . '/' . str_replace('.', '/', $this->layout);

        /** @noinspection PhpIncludeInspection */
        if ($result = @include($layout . $this->layout_extension)) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     * @throws CException
     */
    public function render()
    {
        if (!($this->layout)) {
            mfe::stop(0x00000E3);
        }

        ob_start(function ($layout) {
            return preg_replace_callback(
                '#\{([a-z0-9\-_\#]*?)\}#Si',
                function ($match) {
                    return isset($this->{substr($match[0], 1, -1)}) ? $this->{substr($match[0], 1, -1)} : $match[0];
                }, $layout);
        });

        if (!$this->loadLayout()) {
            mfe::stop(0x00000E3);
            // throw new CException('Not found layout file: ' . $this->layout . $this->layout_extension);
        }
        $this->result = ob_get_contents();
        ob_clean();

        return $this->result;
    }
}
