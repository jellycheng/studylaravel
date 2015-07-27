<?php
/**
// 自 PHP 4.3.0 起可用
set_include_path('/usr/lib/pear');

// 在所有版本的 PHP 中均可用
ini_set('include_path', '/usr/lib/pear');
array_push($includePaths, get_include_path());
set_include_path(join(PATH_SEPARATOR, $includePaths));
*/
//本方法是在php.ini中设置的include_path路径中查找文件，找到返回绝对路径，找不到返回空
echo stream_resolve_include_path('t.php'); //返回 D:\jellyphp\learnlaravel\docs\t.php
