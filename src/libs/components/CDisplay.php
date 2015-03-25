<?php namespace mfe\core\libs\components;

use mfe\core\libs\base\CComponent;

/**
 * Class CDisplay
 * @package mfe\core\libs\components
 */
class CDisplay extends CComponent
{
    /** @var CDisplay */
    static public $instance;

    /**
     * @param $data
     */
    static public function display($data)
    {
        print $data;
    }
}
