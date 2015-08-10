<?php

echo 'start：php5.4才支持<br>'; 
function getClosure2($abstract, $concrete)
{
	//返回一个闭包函数,闭包第1个参数是对象，第2个是传给对象make或者build方法的第2个参数
	return function($c, $parameters = []) use ($abstract, $concrete)
	{
		$method = ($abstract == $concrete) ? 'build' : 'make';
		return $c->$method($concrete, $parameters);
	};
}

$concrete = getClosure2('Illuminate\Contracts\Http\Kernel', 'App\Http\Kernel');
$abc = compact('concrete'); #$abc=array('concrete'=>getClosure2方法返回的闭包)

if ($abc['concrete'] instanceof Closure)
		{
			echo "是闭包<br>";
		}
$obj = new T1();//app对象

$abc['concrete']($obj, array(11,33));//调用闭包


class T1 {

	public function make($concrete, $param) {
		echo $concrete . '<br>'; // App\Http\Kernel
		var_dump($param);
	}
}

echo '<br>end</br>';

