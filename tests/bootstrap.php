<?php (@include_once(__DIR__ . '/../vendor/autoload.php')) or die('Please execute: php composer.phar update' . PHP_EOL);

use mfe\core\Init;
use mfe\core\MfE;

MfE::app(new Init(), __DIR__);
