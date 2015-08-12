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

