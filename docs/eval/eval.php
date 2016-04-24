<?php
//url前缀 对应命名空间  log_errors = On 开启log error_log = d:/php_errors.log 在php.ini中配置错误日志存放位置
header("Content-type: text/html; charset=utf-8");
//error_reporting(0);
//error_reporting(E_ALL ^ E_NOTICE); //不记录警告错误


$content489bc39ff0 = <<<EOT
	<html>
	<p>hello</p>
	
EOT;
$content489bc39ff0 = file_get_contents(__DIR__ . '/txt.txt');
//echo ini_get('error_reporting');
echo ini_get('display_errors');
ini_set('display_errors', 0);//不显示错误
 eval("?> {$content489bc39ff0}");//变量不存在会报警告错误, 除非在当前php中执行error_reporting(0); 或者在  $content489bc39ff0=代码中包含php代码 error_reporting(0);来屏蔽错误

//phpinfo();