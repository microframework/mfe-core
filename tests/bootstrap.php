<?php (@include_once('vendor/autoload.php')) or die('Please execute: php composer.phar update' . PHP_EOL);

use mfe\core\mfe as engine;

engine::app();
