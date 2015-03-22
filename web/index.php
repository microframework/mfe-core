<?php
use mfe\core\applications\WebApplication;

(@include_once('../vendor/autoload.php')) or die('Please execute: php composer.phar update' . PHP_EOL);
/**
 * This file only for micro test, delete it when build
 */

$application = new WebApplication();
$application->run();

WebApplication::app();
