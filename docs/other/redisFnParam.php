<?php

$reflection = new ReflectionClass('Redis');
$methods = $reflection->getMethod('delete');
$param = $methods->getParameters();
var_export($param);

/**

*/