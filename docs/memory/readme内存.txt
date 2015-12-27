

cat /data1/env/php-fpm/lib/php.ini |grep memory_limit
 memory_limit = 128M

 ini_set('memory_limit', '256M');//重置php可以使用的内存大小为64M，一般在远程主机上是不能修改php.ini文件的，只能通过程序设置。注：在safe_mode（安全模式）下，ini_set失效



set_time_limit(600);//设置php执行时间，超时限制为６分钟

$m=memory_get_usage(); //获取当前占用内存
