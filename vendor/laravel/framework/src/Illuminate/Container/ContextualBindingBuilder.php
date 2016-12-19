<?php namespace Illuminate\Container;

use Illuminate\Contracts\Container\ContextualBindingBuilder as ContextualBindingBuilderContract;
//上下文构建对象
class ContextualBindingBuilder implements ContextualBindingBuilderContract {

	/**
	 * The underlying container instance.
	 *
	 * @var \Illuminate\Container\Container
	 */
	protected $container;

	/**
	 * The concrete instance.
	 *
	 * @var string
	 */
	protected $concrete;

	/**
	 * Create a new contextual binding builder.
	 *
	 * @param  \Illuminate\Container\Container  $container容器对象
	 * @param  string  $concrete 实现物,具体物
	 * @return void
	 */
	public function __construct(Container $container, $concrete)
	{
		$this->concrete = $concrete;
		$this->container = $container;
	}

	/**
	 * Define the abstract target that depends on the context.
	 *
	 * @param  string  $abstract
	 * @return $this
	 */
	public function needs($abstract)
	{
		$this->needs = $abstract;

		return $this;
	}

	/**
	 * Define the implementation for the contextual binding.
	 * 设置容器的上下文属性：$this->contextual[$concrete][$abstract] = $implementation;
	 * @param  \Closure|string  $implementation
	 * @return void
	 */
	public function give($implementation)
	{
		$this->container->addContextualBinding($this->concrete, $this->needs, $implementation);
	}

}
