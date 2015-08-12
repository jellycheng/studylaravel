<?php


function share(Closure $closure)
{	//返回闭包函数
	return function($container) use ($closure)
	{
		// We'll simply declare a static variable within the Closures and if it has
		// not been set we will execute the given Closures to resolve this value
		// and return it back to these consumers of the method as an instance.
		static $object;

		if (is_null($object))
		{
			$object = $closure($container);
		}

		return $object;
	};
}
//返回一个闭包函数
$abc = share(function($app) {
			
			echo 'hello ' . $app;

		});
//var_dump($abc);

$abc("world"); //调用闭包函数，并传入world参数，输出 hello world

