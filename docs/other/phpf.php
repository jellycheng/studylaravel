<?php

function hello($a, $b) {
	echo "hello world" . $a . $b;
}


$callback = 'hello';
$obj = new ReflectionFunction($callback); //反射方法
$dataA = $obj->getParameters();
foreach($dataA as $k=>$v) {
	echo 'k='.$k .', v='. $v . '<br>';
}

//$callback = array('aCls', 'aFun'); //array('类名', '方法名');
//$obj2 = new ReflectionMethod($callback[0], $callback[1]);//反射类名中的方法名

