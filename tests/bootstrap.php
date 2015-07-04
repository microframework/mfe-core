<?php (require_once dirname(__DIR__) . '/vendor/autoload.php');

define('MFE_DEBUG', false);

use mfe\core\MfE;

MfE::$DEBUG = false;
$application = MfE::app();
ini_set('display_errors', true);

