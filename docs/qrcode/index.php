<?php
include './spreadImage.php';
//extension=php_gd2.dll
$obj = new spreadImage();
$nickanme = "中国";
$mobile = "13712345678";
$avatar = "./img/icon_fn.jpg"; //头像
$ticket = "./img/qrcode.jpg"; //二维码
$templateFile = "temple_fn.jpg";
$outputFile = "./dst/aaa.jpg"; //
$obj->get($nickanme, $mobile, $avatar, $ticket, $templateFile, $outputFile);


