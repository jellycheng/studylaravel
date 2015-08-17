<?php
//静态延迟实例化 

require_once 'subB.php';

$obj = subB::hello("jellyfun", array(1,3,4));

$p = $obj->getDebugParam();

var_export($p);
/**

array (
  'sName' => 'hello',
  'aParams' => array (
					0 => 'jellyfun',
					1 => array (
					  0 => 1,
					  1 => 3,
					  2 => 4,
					),
				  ),
  'iParamsCount' => 2,
)
*/
