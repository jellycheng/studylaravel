<?php namespace Illuminate\Support\Facades;

/**
 * @see \Illuminate\Filesystem\Filesystem
 */
class TestJelly extends Facade {

	/**
	 * Get the registered name of the component.
	 * 返回facade名如 testjelly 则是app对象实例化属性的key
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'testjelly'; }

}
