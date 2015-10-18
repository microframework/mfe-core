<?php namespace mfe\core\libs\system\page;
use mfe\core\MfE;

/**
 * Class SystemMfEPage
 *
 * @package mfe\core\libs\system\page
 */
class SystemMfEPage
{
    private $ENGINE_NAME = MfE::ENGINE_NAME;
    private $ENGINE_VERSION = MfE::ENGINE_VERSION;

    public function __toString()
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>{$this->ENGINE_NAME}::v.{$this->ENGINE_VERSION}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            border: 0;
            outline: 0;
        }

        html, body {
            padding: 10px;
            background: #f2f2f2;
        }
    </style>
</head>
<body>
    TEST PAGE
</body>
</html>
HTML;
    }
}
