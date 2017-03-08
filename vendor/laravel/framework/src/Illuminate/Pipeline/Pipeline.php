<?php namespace Illuminate\Pipeline;

use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pipeline\Pipeline as PipelineContract;

class Pipeline implements PipelineContract {

	/**
	 * The container implementation.
	 *
	 * @var \Illuminate\Contracts\Container\Container
	 */
	protected $container;

	/**
	 * The object being passed through the pipeline.
	 * 请求对象
	 * @var mixed
	 */
	protected $passable;

	/**
	 * The array of class pipes.
	 *
	 * @var array
	 */
	protected $pipes = array();

	/**
	 * The method to call on each pipe.
	 *
	 * @var string
	 */
	protected $method = 'handle';

	/**
	 * Create a new class instance.
	 *
	 * @param  \Illuminate\Contracts\Container\Container  $container 容器app对象
	 * @return void
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Set the object being sent through the pipeline.
	 *
	 * @param  mixed  $passable  请求对象
	 * @return $this
	 */
	public function send($passable)
	{
		$this->passable = $passable;
		return $this;
	}

	/**
	 * Set the array of pipes.
	 * 管道，所有中间介
	 * @param  dynamic|array  $pipes
	 * @return $this
	 */
	public function through($pipes)
	{
		$this->pipes = is_array($pipes) ? $pipes : func_get_args();
		return $this;
	}

	/**
	 * Set the method to call on the pipes.
	 *
	 * @param  string  $method
	 * @return $this
	 */
	public function via($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * Run the pipeline with a final destination callback.
	 * @param  \Closure  $destination
	 * @return mixed
	 */
	public function then(Closure $destination)
	{
		$firstSlice = $this->getInitialSlice($destination);
		$pipes = array_reverse($this->pipes);//中间介颠倒顺序
		//调用所有中间介类的handle方法
		return call_user_func(
							array_reduce($pipes, $this->getSlice(), $firstSlice),
							$this->passable //请求对象
						);
	}

	/**
	 * Get a Closure that represents a slice of the application onion.
	 * @return \Closure
	 */
	protected function getSlice()
	{	//$pipe=当前单元值如 'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode'或闭包
		return function($stack, $pipe)
		{
			return function($passable) use ($stack, $pipe)
			{
				if ($pipe instanceof Closure)
				{
					return call_user_func($pipe, $passable, $stack);
				} else {
				    //执行管道的handle(请求对象, 闭包)  此闭包接收请求对象
					return $this->container->make($pipe)->{$this->method}($passable, $stack);
				}
			};
		};
	}

	/**
	 * Get the initial slice to begin the stack call.
	 * @param  \Closure  $destination
	 * @return \Closure
	 */
	protected function getInitialSlice(Closure $destination)
	{
		return function($passable) use ($destination)
		{
			return call_user_func($destination, $passable);
		};
	}

}
