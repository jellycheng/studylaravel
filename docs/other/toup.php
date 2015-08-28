<?php
$name = "abcg_a-d"; //Abcg_A-D
echo $name . '<br>';
echo ucfirst(preg_replace_callback('/([_-])([a-zA-Z])/', function($match){return strtoupper($match[0]);}, $name));
echo "<br>";
echo ucfirst(preg_replace_callback('/([_-])([a-zA-Z])/', function($match){return '\\'.strtoupper($match[2]);}, $name));
