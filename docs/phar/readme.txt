

PHP5.3之后支持了类似Java的jar包，名为phar。用来将多个PHP文件打包为一个文件
修改php.ini 
	phar.readonly = Off 这样才能打包成phar文件

cd 到本目录 然后执行 composer install

php build.php

