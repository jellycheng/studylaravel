<?php


function share(Closure $closure)
{	//返回闭包函数,且返回的函数执行之后返回内容不是null则最多执行一次,重复执行跟第一次执行的内容一样
	return function($container) use ($closure)
	{
		//闭包返回的内容,共享
		static $object;
		if (is_null($object))
		{//已经执行过,则不再执行
			$object = $closure($container);
		}

		return $object;
	};
}
//返回一个闭包函数
$abc = share(function($app) {
			echo 'hello ' . $app . PHP_EOL;
			return 'ok ' . $app . PHP_EOL;
		});
//var_dump($abc);

echo $abc("world"); //调用闭包函数，输出 hello world  ok world
echo $abc("jelly"); // 输出 ok world

