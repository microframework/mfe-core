<?php
use mfe\core\applications\WebApplication;

(@include_once(dirname(__DIR__) . '/vendor/autoload.php')) or die('Please execute: php composer.phar update' . PHP_EOL);
/**
 * This file only for micro test, delete it when build
 */

$application = new WebApplication(true);
$application->run();

//WebApplication::app();
