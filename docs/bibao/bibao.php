<?php

class Xyz{

	//$key=字符串， $value=函数、闭包函数或者字符串，如果是字符串会自动转成匿名函数
	public function abc($key, $value)
	{
		
		if ( ! $value instanceof Closure)
		{#不是闭包函数则生成一个闭包函数
			$value = function() use ($value)
			{
				return $value;
			}; //注意 匿名函数的最后分号不能少，否则报语法错误
		}

		$this->bind($key, $value);
	}
	//$key = 字符串， $clo=闭包函数
	public function bind($key, $clo){
		
	
	}

}

function jelly($param) {
	var_dump($param);
}

function abcd($callback, array $parameters = [])
{//返回闭包函数，匿名函数
	return function() use ($callback, $parameters)
	{
		return call_user_func($callback, $parameters);
	};
}
$abc = abcd("jelly", array(1,3,5));

if($abc instanceof Closure){#为真
	echo "is Closure";
} else {
	echo "not Closure";
}


$greet = function($name)
{
    printf("Hello %s\r\n", $name);
};   //注意 匿名函数的最后分号不能少，否则报语法错误

if($greet instanceof Closure){#为真
	echo "<br>greet is Closure";
}

