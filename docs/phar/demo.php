<?php
//使用phar压缩包
include 'jellytest.phar';
include 'phar://jellytest.phar/src/Jelly/page.php'; #phar://紧跟着是phar文件/phar文件中的目录结构文件

echo hello();

echo "<br>";

$obj = new \Jelly\user(); 
$data = $obj->setUserName('jelly')->getAll();
var_export($data);

echo "<br>";
page_hello();
