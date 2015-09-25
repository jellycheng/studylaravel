<?php

function hello($a1, $a2) {

	return $a1 . ' | ' . $a2;
}
/**
mixed call_user_func_array ( callable $callback , array('传给$callback的参数1', '传给$callback的参数n'))
call_user_func_array(array($fo对象, "bar方法名"), array("three", "four"));
*/
$str = call_user_func_array("hello", array('arg1', 'arg2'));
echo $str;//arg1 | arg2


$abc = array(array(1,3,5 ,'h'=>'h111') , array(2,4, 'h'=>'h2222'));
$ary = call_user_func_array( 'array_merge', $abc );
var_export($ary);//array ( 0 => 1, 1 => 3, 2 => 5, 'h' => 'h2222', 3 => 2, 4 => 4, )
