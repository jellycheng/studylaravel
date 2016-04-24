<?php
include 'Event.php';

$command = "aaa bbb";
$cmd = PHP_BINARY.' artisan '.$command;
$obj = new \Jelly\Console\Scheduling\Event($cmd);
$obj->run();

