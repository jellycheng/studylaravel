<?php
require 'vendor/autoload.php';
include './src/Jelly/page.php';

echo hello();

echo "<br>";

$obj = new \Jelly\user(); 
$data = $obj->setUserName('jelly')->getAll();
var_export($data);


page_hello();

