<?php namespace Jelly\Config;

interface RepositoryInterface {

	/**
	 * Determine if the given configuration value exists.
	 * 是否存在配置key
	 * @param  string  $key
	 * @return bool
	 */
	public function has($key);

	/**
	 * Get the specified configuration value.
	 * 获取配置key的值，不存在返回默认值
	 * @param  string  $key
	 * @param  mixed   $default默认值
	 * @return mixed
	 */
	public function get($key, $default = null);

	/**
	 * Set a given configuration value.
	 * 设置配置值
	 * @param  array|string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function set($key, $value = null);

	/**
	 * Prepend a value onto an array configuration value.
	 * 配置前追加
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function prepend($key, $value);

	/**
	 * Push a value onto an array configuration value.
	 * 配置尾部追加
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function push($key, $value);

}
