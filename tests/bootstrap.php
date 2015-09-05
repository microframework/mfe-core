<?php (require_once dirname(__DIR__) . '/vendor/autoload.php');

(defined('MFE_DEBUG')) or define('MFE_DEBUG', false);
(defined('MFE_SERVER')) or define('MFE_SERVER', true);

use mfe\core\MfE;

MfE::$DEBUG = false;
$application = MfE::app();
ini_set('display_errors', true);

