<?php namespace Illuminate\Foundation;

use Closure;
//环境检测器
class EnvironmentDetector {

	/**
	 * Detect the application's current environment.
	 *
	 * @param  \Closure  $callback
	 * @param  array|null  $consoleArgs 接收到的cli参数
	 * @return string
	 */
	public function detect(Closure $callback, $consoleArgs = null)
	{
		if ($consoleArgs)
		{//cli方式
			return $this->detectConsoleEnvironment($callback, $consoleArgs);
		}
        //非cli方式
		return $this->detectWebEnvironment($callback);
	}

	/**
	 * Set the application environment for a web request.
	 *
	 * @param  \Closure  $callback
	 * @return string
	 */
	protected function detectWebEnvironment(Closure $callback)
	{
		return call_user_func($callback);
	}

	/**
	 * Set the application environment from command-line arguments.
	 * cli方式
	 * @param  \Closure  $callback
	 * @param  array  $args
	 * @return string
	 */
	protected function detectConsoleEnvironment(Closure $callback, array $args)
	{
		//查找数组值以--env开头的字符串并返回
		if ( ! is_null($value = $this->getEnvironmentArgument($args)))
		{//找到 --env=dev
			return head(array_slice(explode('=', $value), 1));//返回dev
		}

		return $this->detectWebEnvironment($callback);
	}

	/**
	 * Get the environment argument from the console.
	 * 查找数组值以--env开头的字符串并返回
	 * @param  array  $args 返回数组中值以--env开头的单元值
	 * @return string|null  如 --env=dev 或null
	 */
	protected function getEnvironmentArgument(array $args)
	{
		return array_first($args, function($k, $v)
		{
			return starts_with($v, '--env');
		});
	}

}
