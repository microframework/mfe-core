<?php namespace mfe\core\libs\components;

use mfe\core\api\applications\IApplication;
use mfe\core\libs\base\CComponent;

/**
 * Class CDisplay
 *
 * @package mfe\core\libs\components
 */
class CDisplay extends CComponent
{
    const TYPE_EMITTER_SAPI = 'SApi';
    const TYPE_EMITTER_SERIAL = 'Serialized';

    const EmitterNamespace = 'mfe\\core\\libs\\http\\emitters';

    /**
     * @param IApplication $application
     * @param string $type
     * @param null $bufferLevel
     */
    static public function display(IApplication $application, $type = self::TYPE_EMITTER_SAPI, $bufferLevel = null)
    {
        $emitter = static::EmitterNamespace . '\\' . $type . 'Emitter';
        new $emitter($application->response, $bufferLevel);
    }
}
