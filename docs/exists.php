<?php

header("Content-type: text/html; charset=utf-8");

$b = class_exists('Phar');
echo '<br>Phar: ';
var_dump($b);

echo '<br>opcache: ';
$b = function_exists('opcache_compile_file');
var_dump($b);

