<?php

$prefix = "Symfony\\Component\\Yaml\\";
echo $prefix[0] . '<br>'; //取字符串第1个字符， 输出S

#访问url http://localhost/learnlaravel/docs/t.php?a=1&b=2
echo $_SERVER['REQUEST_URI'] . "<br>";#输出 /learnlaravel/docs/t.php?a=1&b=2
$uri = urldecode(
	parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);
echo $uri . '<br>';#/learnlaravel/docs/t.php



