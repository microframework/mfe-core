<?php namespace mfe;
/* TEST CASE
function srt(CmfeObjectsStack $stack){
    $result = '|';
    for($j = 0; $j < 10; $j++) {
        $result .= $j . '|';
    }
    $result .= PHP_EOL.'|';
    foreach($stack as $key => $value){
        $result .= $value . '|';
    }
    print $result.PHP_EOL;
}

$stack = new CmfeObjectsStack();
$stack->add(0, 0);
$stack->add(1, 1);
$stack->add(2, 2);
$stack->add(3, 3);
$stack->add(4, 4);
$stack->add(5, 5);
$stack->add(6, 6);
$stack->add(7, 7);
$stack->add(8, 8);
$stack->add(9, 9);
srt($stack);
echo str_pad('-', 22, '-').PHP_EOL;
$stack->down(3, -10);
srt($stack);
*/
