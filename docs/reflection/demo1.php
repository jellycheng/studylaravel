<?php

spl_autoload_register(function($class) {echo $class;});

$obj = new ReflectionClass('abc');//反射的类不存在时，也会触法php自动加载器

