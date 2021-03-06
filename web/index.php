<?php (@include_once('../vendor/autoload.php')) or die('Please execute: php composer.phar update' . PHP_EOL);
/**
 * This file only for micro test, delete it when build
 */

use mfe\core\core\Page;
use mfe\core\mfe as engine;

/** @var Page $page */
$page = engine::app()->page;

$page->setLayout('test');
$page->_content = "<p>Hello World!</p>";
$page->render();

engine::display($page);

if (class_exists('Lua')) {
    $lua = new \Lua();
    $lua->eval("print('Hello World!');");
}
