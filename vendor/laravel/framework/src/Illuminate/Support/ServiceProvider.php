<?php namespace Illuminate\Support;

use BadMethodCallException;
//服务提供者基类
abstract class ServiceProvider {

	/**
	 * The application instance.
	 * app对象
	 * @var \Illuminate\Contracts\Foundation\Application
	 */
	protected $app;

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * The paths that should be published.
	 *
	 * @var array
	 */
	protected static $publishes = [];

	/**
	 * The paths that should be published by group.
	 *
	 * @var array
	 */
	protected static $publishGroups = [];

	/**
	 * Create a new service provider instance.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app 对象
	 * @return void
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}

	/**
	 * Register the service provider.
	 * 负责注册相关代码，不执行业务
	 * @return void
	 */
	abstract public function register();

	/**
	 * Merge the given configuration with the existing configuration.
	 * 为$key新增配置($path上配置文件目录+文件)，但不覆盖原来的配置
	 * @param  string  $path 配置文件
	 * @param  string  $key  配置key
	 * @return void
	 */
	protected function mergeConfigFrom($path, $key)
	{
		$config = $this->app['config']->get($key, []);//获取现有配置

		$this->app['config']->set($key, array_merge(require $path, $config));
	}

	/**
	 * Register a view file namespace.
	 *
	 * @param  string  $path 目录
	 * @param  string  $namespace 命名空间名
	 * @return void
	 */
	protected function loadViewsFrom($path, $namespace)
	{
		if (is_dir($appPath = $this->app->basePath().'/resources/views/vendor/'.$namespace))
		{
			$this->app['view']->addNamespace($namespace, $appPath);
		}
		$this->app['view']->addNamespace($namespace, $path);
	}

	/**
	 * Register a translation file namespace.
	 *
	 * @param  string  $path
	 * @param  string  $namespace
	 * @return void
	 */
	protected function loadTranslationsFrom($path, $namespace)
	{
		$this->app['translator']->addNamespace($namespace, $path);
	}

	/**
	 * Register paths to be published by the publish command.
	 * 注册路径由发布命令
	 * @param  array  $paths
	 * @param  string  $group
	 * @return void
	 */
	protected function publishes(array $paths, $group = null)
	{
		$class = get_class($this);//获取本类对象的类名，如果是子类就是子类名

		if ( ! array_key_exists($class, static::$publishes))
		{//不存在，声明一个数组
			static::$publishes[$class] = [];
		}

		static::$publishes[$class] = array_merge(static::$publishes[$class], $paths);
		if ($group)
		{
			static::$publishGroups[$group] = $paths;
		}
	}

	/**
	 * Get the paths to publish.
	 * 路径来发布，获取配置
	 * @param  string  $provider
	 * @param  string  $group
	 * @return array
	 */
	public static function pathsToPublish($provider = null, $group = null)
	{
		if ($group && array_key_exists($group, static::$publishGroups))
		{
			return static::$publishGroups[$group];
		}

		if ($provider && array_key_exists($provider, static::$publishes))
		{
			return static::$publishes[$provider];
		}

		if ($group || $provider)
		{
			return [];	
		}
        //获取所有配置
		$paths = [];
		foreach (static::$publishes as $class => $publish)
		{
			$paths = array_merge($paths, $publish);
		}

		return $paths;
	}

	/**
	 * Register the package's custom Artisan commands.
	 *
	 * @param  array  $commands
	 * @return void
	 */
	public function commands($commands)
	{
		$commands = is_array($commands) ? $commands : func_get_args();
		$events = $this->app['events'];
		$events->listen('artisan.start', function($artisan) use ($commands)
		{//监听事件
			$artisan->resolveCommands($commands);
		});
	}

	/**
	 * Get the services provided by the provider.
	 * 配置“服务提供者名即抽象物名”,多个写在多个单元中
	 * return ['服务提供者名1如Riak\Contracts\Connection', '服务提供者名N'];
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

	/**
	 * Get the events that trigger this service provider to register.
	 * 配置监听事件名
	 * @return array = ['监听事件名1', '监听事件名N']
	 */
	public function when()
	{
		return [];
		#1.无需监听事件则返回空数组
		#2.设置监听事件，当触发事件时触发服务提供者register()方法
	}

	/**
	 * Determine if the provider is deferred.
	 *
	 * @return bool
	 */
	public function isDeferred()
	{
		return $this->defer;
	}

	/**
	 * Get a list of files that should be compiled for the package.
	 *
	 * @return array
	 */
	public static function compiles()
	{
		return [];
	}

	/**
	 * Dynamically handle missing method calls.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if ($method == 'boot') return;

		throw new BadMethodCallException("Call to undefined method [{$method}]");
	}

}
