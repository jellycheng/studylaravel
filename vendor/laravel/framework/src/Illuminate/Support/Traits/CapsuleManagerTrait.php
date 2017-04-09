<?php namespace Illuminate\Support\Traits;

use Illuminate\Support\Fluent;
use Illuminate\Contracts\Container\Container;

trait CapsuleManagerTrait {

	/**
	 * The current globally used instance.
	 * 所有对象共用当前实例对象
	 * @var object
	 */
	protected static $instance;

	/**
	 * The container instance.
	 * app对象,容器对象
	 * @var \Illuminate\Contracts\Container\Container
	 */
	protected $container;

	/**
	 * Setup the IoC container instance.
	 * 设置app对象,容器对象
	 * @param  \Illuminate\Contracts\Container\Container  $container
	 * @return void
	 */
	protected function setupContainer(Container $container)
	{
		$this->container = $container;

		if ( ! $this->container->bound('config'))
		{//如果没绑定config,绑定config对象
			$this->container->instance('config', new Fluent);
		}
	}

	/**
	 * Make this capsule instance available globally.
	 * 注入本类对象,使后续所有对象共用本类对象
	 * @return void
	 */
	public function setAsGlobal()
	{
		static::$instance = $this;
	}

	/**
	 * Get the IoC container instance.
	 * 获取app对象,容器对象
	 * @return \Illuminate\Contracts\Container\Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Set the IoC container instance.
	 * 设置app对象,容器对象
	 * @param  \Illuminate\Contracts\Container\Container  $container
	 * @return void
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}

}
