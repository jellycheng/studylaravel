<?php
$phar = new Phar('jellytest.phar');//指定压缩包的名称
$phar->startBuffering();
$phar->buildFromDirectory(__DIR__, '$(src|vendor)/.*\.php$');//第1个参数 指定压缩的目录， 第2个参数 通过正则来制定压缩文件的扩展名
//$phar->compressFiles(Phar::GZ); 指定压缩格式，Phar::GZ表示使用gzip来压缩此文件。也支持bz2压缩。参数修改为 PHAR::BZ2即可
$phar->setStub($phar->createDefaultStub('./vendor/autoload.php')); #设置启动加载的文件。默认会自动加载并执行./vendor/autoload.php
$phar->stopBuffering();



/**
使用phar压缩包
include 'jellytest.phar';
include 'jellytest.phar/code/page.php';

*/


