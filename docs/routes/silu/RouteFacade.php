<?php
namespace Jelly;

//本类都是静态方法和静态属性
class RouteFacade {

	protected static $resolvedInstance;
	
	protected static function resolveFacadeInstance($name)
	{
		if (is_object($name)) return $name;

		if (isset(static::$resolvedInstance[$name]))
		{
			return static::$resolvedInstance[$name];
		}

		return static::$resolvedInstance[$name] = new $name;
	}


	public static function __callStatic($method, $args)
	{
		//$instance = static::getFacadeRoot();
		$instance = static::resolveFacadeInstance("Jelly\\Route");
		
		switch (count($args))
		{
			case 0:
				return $instance->$method();

			case 1:
				return $instance->$method($args[0]);

			case 2:
				return $instance->$method($args[0], $args[1]);

			case 3:
				return $instance->$method($args[0], $args[1], $args[2]);

			case 4:
				return $instance->$method($args[0], $args[1], $args[2], $args[3]);

			default:
				return call_user_func_array(array($instance, $method), $args);
		}
	}


}
