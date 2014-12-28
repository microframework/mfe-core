<?php
/**
 * This file only for micro test, delete it when build
 */

require_once 'mfe.engine.php';

use mfe\mfe;

/** @var \mfe\Page $page*/
$page = mfe::app()->page;

$page->setLayout('test');
$page->_content = "<p>Hello World!</p>";

mfe::display($page);
