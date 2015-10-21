<?php (@include_once(dirname(__DIR__) . '/src/MfE.php')) or die('Whats wrong!' . PHP_EOL);
/**
 * This file only for micro test, delete it when build
 */

//use mfe\core\core\Page;
//use mfe\core\MfE as engine;

///** @var Page $page */
//$page = engine::app()->page;
//
//if ($page) {
//    $page->setLayout('test');
//    $page->_content = "<p>Hello World!</p>";
//    $page->render();
//
//    engine::display($page);
//}


class Example
{
    public $test = 'Hello';

    public function __construct()
    {

    }

}

$box = new \mfe\core\libs\system\IoC();

$box->singleton([
    'class' => Example::class,
    'test' => 'Hello World'
]);

$a = $box->make();
$a->test = 'Bu';
echo $a->test;

$b = $box->make('Example');
echo $b->test;

//if (class_exists('Lua')) {
//    $lua = new \Lua();
//    $lua->eval("print('Hello World!');");
//}
