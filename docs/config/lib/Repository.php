<?php namespace Jelly\Config;

use ArrayAccess;
use Jelly\Config\RepositoryInterface as ConfigContract;

class Repository implements ArrayAccess, ConfigContract {

	/**
	 * All of the configuration items.
	 * 配置数据
	 * @var array
	 */
	protected $items = [];

	/**
	 * Create a new configuration repository.
	 * 配置数据
	 * @param  array  $items
	 * @return void
	 */
	public function __construct(array $items = array())
	{
		$this->items = $items;
	}

	/**
	 * Determine if the given configuration value exists.
	 * 
	 * @param  string  $key=abc || a.b.c || app.config
	 * @return bool
	 */
	public function has($key)
	{
		$array = $this->items;
		if (empty($array) || is_null($key)) return false;

		if (array_key_exists($key, $array)) return true;

		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_array($array) || ! array_key_exists($segment, $array))
			{
				return false;
			}

			$array = $array[$segment];
		}

		return true;
	}

	/**
	 * Get the specified configuration value.
	 *
	 * @param  string  $key=abc || a.b.c || app.config
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		$array = $this->items;
		if (is_null($key)) return $array;

		if (isset($array[$key])) return $array[$key];

		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_array($array) || ! array_key_exists($segment, $array))
			{
				return $this->_value($default);
			}

			$array = $array[$segment];
		}

		return $array;
	}

	/**
	 * Set a given configuration value.
	 *
	 * @param  array|string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function set($key, $value = null)
	{
		if (is_array($key))
		{
			foreach ($key as $innerKey => $innerValue)
			{
				$this->_array_set($this->items, $innerKey, $innerValue);
			}
		}
		else
		{
			$this->_array_set($this->items, $key, $value);
		}
	}

	protected function _array_set(&$array, $key, $value) {
		if (is_null($key)) return $array = $value;
		$keys = explode('.', $key);
		while (count($keys) > 1)
		{// $keys=a.b.c
			$key = array_shift($keys);//将数组开头的单元移出数组

			// 
			if ( ! isset($array[$key]) || ! is_array($array[$key]))
			{
				$array[$key] = [];
			}

			$array =& $array[$key];
		}

		$array[array_shift($keys)] = $value;

		return $array;
	}
	/**
	 * Prepend a value onto an array configuration value.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function prepend($key, $value)
	{
		$array = $this->get($key);//获取原来值

		array_unshift($array, $value);//在数组开头插入一个或多个单元

		$this->set($key, $array);
	}

	/**
	 * Push a value onto an array configuration value.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function push($key, $value)
	{
		$array = $this->get($key);

		$array[] = $value;

		$this->set($key, $array);
	}

	/**
	 * Get all of the configuration items for the application.
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->items;
	}

	/**
	 * Determine if the given configuration option exists.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return $this->has($key);
	}

	/**
	 * Get a configuration option.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->get($key);
	}

	/**
	 * Set a configuration option.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * Unset a configuration option.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		$this->set($key, null);
	}

	protected function _value($value)
	{
		return $value instanceof Closure ? $value() : $value;
	}

}
