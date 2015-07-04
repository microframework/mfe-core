<?php namespace mfe\core\libs\components;

use mfe\core\libs\base\CComponent;

/**
 * Class CDisplay
 * @package mfe\core\libs\components
 */
class CDisplay extends CComponent
{
    const TYPE_PAGE = 'page';
    const TYPE_DEBUG = 'debug';

    /** @var CDisplay */
    static public $instance;

    /**
     * @param $data
     * @param $type
     */
    static public function display($data, $type = self::TYPE_PAGE)
    {
        print $data;
    }
}
