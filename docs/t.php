<?php

$prefix = "Symfony\\Component\\Yaml\\";
echo $prefix[0] . '<br>'; //取字符串第1个字符， 输出S

#访问url http://localhost/learnlaravel/docs/t.php?a=1&b=2
echo $_SERVER['REQUEST_URI'] . "<br>";#输出 /learnlaravel/docs/t.php?a=1&b=2
$uri = urldecode(
	parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);
echo $uri . '<br>';#/learnlaravel/docs/t.php




$mystring = 'abc';
$findme   = 'a';
$pos = strpos($mystring, $findme); //查找字符串首次出现的位置,区分大小写，未找到则返回false
var_dump($pos);// 0 首次出现的位置  

$findme   = 'B';
$pos = strpos($mystring, $findme);
var_dump($pos);//false  说明strpos区分小写匹配



$city  = "San Francisco";
$state = "CA";
$event = "";

$location_vars = array("city", "state", 'jelly2');

$result = compact("event", "abc_here", $location_vars); #做的事情是$enent变量定义则event作为$result数组的key，其值作为值，如果变量没有定义则丢弃，如果是数组的递归判断
print_r($result);#Array ( [event] => '' [city] => San Francisco [state] => CA )


