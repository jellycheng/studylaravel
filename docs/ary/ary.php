<?php

//函数将会使用「点」符号从深度嵌套数组取回给定的值
function array_get($array, $key, $default = null) {
	if (is_null($key)) return $array;

	if (isset($array[$key])) return $array[$key];

	foreach (explode('.', $key) as $segment)
	{// a.b.c
		if ( ! is_array($array) || ! array_key_exists($segment, $array))
		{//不存在的key则返回默认值 
			return value($default);
		}

		$array = $array[$segment];
	}

	return $array;
}

if ( ! function_exists('value'))
{
	/**
	 * Return the default value of the given value.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	function value($value)
	{
		return $value instanceof Closure ? $value() : $value;
	}
}

$array = ['names' => ['joe' => ['programmer']]];

$value = array_get($array, 'names.joe'); //array ( 0 => 'programmer', )

echo var_export($value, true) . '<br>';



$value = array_get($array, 'names.john', 'default'); //default
echo $value . '<br>';