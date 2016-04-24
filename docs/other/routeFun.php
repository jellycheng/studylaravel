<?php

$uri = "abc{xyz?}hello";
$uri = preg_replace('/\{(\w+?)\?\}/', '{$1}', $uri); //把{xyz?}替换成{xyz}
echo $uri;//abc{xyz}hello

$uri2 = "abc{xyz?}hello";
preg_match_all('/\{(\w+?)\?\}/', $uri2, $matches);
var_export($matches);
/**
array (
  0 => array (
    0 => '{xyz?}',
  ),
  1 => array (
    0 => 'xyz',
  ),
)
*/
$newA = array_fill_keys($matches[1], array());//array array_fill_keys ( array $keys , mixed $value )  $keys数组的key作为新数组key且对应的值是$value
var_export($newA);
/**
array (
  'xyz' =>  array (  ),
)
*/



$uri3 = "http://xxx.com/{abc}/{xyz?}/haha?hello";
preg_match_all('/\{(.*?)\}/', $uri3, $matches);
$uri3 = array_map(function($m) { return trim($m, '?'); }, $matches[1]);
var_export($uri3);
/**
array (
  0 => 'abc',
  1 => 'xyz',
)
*/


