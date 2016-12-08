<?php namespace Illuminate\Foundation;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Routing\RoutingServiceProvider;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;

class Application extends Container implements ApplicationContract, HttpKernelInterface {

	/**
	 * The Laravel framework version.
	 *
	 * @var string
	 */
	const VERSION = '5.0.16';

	/**
	 * The base path for the Laravel installation.
	 *
	 * @var string
	 */
	protected $basePath;

	/**
	 * Indicates if the application has been bootstrapped before.
	 *
	 * @var bool
	 */
	protected $hasBeenBootstrapped = false;

	/**
	 * Indicates if the application has "booted".
	 *
	 * @var bool
	 */
	protected $booted = false;

	/**
	 * The array of booting callbacks.
	 *
	 * @var array
	 */
	protected $bootingCallbacks = array();

	/**
	 * The array of booted callbacks.
	 *
	 * @var array
	 */
	protected $bootedCallbacks = array();

	/**
	 * The array of terminating callbacks.
	 *
	 * @var array
	 */
	protected $terminatingCallbacks = array();

	/**
	 * All of the registered service providers.
	 * 存已经实例化的服务提供者对象
	 * @var array['对象1', '对象n']
	 */
	protected $serviceProviders = array();

	/**
	 * The names of the loaded service providers.
	 *
	 * @var array
	 */
	protected $loadedProviders = array();

	/**
	 * The deferred services and their providers.
	 *
	 * @var array
	 */
	protected $deferredServices = array();

	/**
	 * The custom storage path defined by the developer.
	 *
	 * @var string
	 */
	protected $storagePath;

	/**
	 * The environment file to load during bootstrapping.
	 *
	 * @var string
	 */
	protected $environmentFile = '.env';

	/**
	 * Create a new Illuminate application instance.
	 *
	 * @param  string|null  $basePath
	 * @return void
	 */
	public function __construct($basePath = null)
	{
	    //注册基本绑定，设置本类对象
		$this->registerBaseBindings();
		//注册基础服务提供者： 1.事件服务提供者，2.路由服务提供者
		$this->registerBaseServiceProviders();

		$this->registerCoreContainerAliases();//设置别名
		//本类对象->basePath()获取的是$basePath值
		if ($basePath) $this->setBasePath($basePath);
	}

	/**
	 * Get the version number of the application.
	 *
	 * @return string
	 */
	public function version()
	{
		return static::VERSION;
	}

	/**
	 * Register the basic bindings into the container.
	 *
	 * @return void
	 */
	protected function registerBaseBindings()
	{
	    //设置本类的$instance属性值为本类对象
		static::setInstance($this);
        //设置instances属性key=》值
		$this->instance('app', $this);
		$this->instance('Illuminate\Container\Container', $this);
	}

	/**
	 * Register all of the base service providers.
	 *
	 * @return void
	 */
	protected function registerBaseServiceProviders()
	{
		//调用服务提供者类的register()方法
		$this->register(new EventServiceProvider($this));

		$this->register(new RoutingServiceProvider($this));
	}

	/**
	 * Run the given array of bootstrap classes.
	 *
	 * @param  array  $bootstrappers
	 * @return void
	 */
	public function bootstrapWith(array $bootstrappers)
	{
		foreach ($bootstrappers as $bootstrapper)
		{
			$this['events']->fire('bootstrapping: '.$bootstrapper, [$this]);

			$this->make($bootstrapper)->bootstrap($this);

			$this['events']->fire('bootstrapped: '.$bootstrapper, [$this]);
		}

		$this->hasBeenBootstrapped = true;
	}

	/**
	 * Register a callback to run after loading the environment.
	 *
	 * @param  \Closure  $callback
	 * @return void
	 */
	public function afterLoadingEnvironment(Closure $callback)
	{
		return $this->afterBootstrapping('Illuminate\Foundation\Bootstrap\DetectEnvironment', $callback );
	}

	/**
	 * Register a callback to run before a bootstrapper.
	 *
	 * @param  string  $bootstrapper
	 * @param  Closure  $callback
	 * @return void
	 */
	public function beforeBootstrapping($bootstrapper, Closure $callback)
	{
		$this['events']->listen('bootstrapping: '.$bootstrapper, $callback);
	}

	/**
	 * Register a callback to run after a bootstrapper.
	 *
	 * @param  string  $bootstrapper
	 * @param  Closure  $callback
	 * @return void
	 */
	public function afterBootstrapping($bootstrapper, Closure $callback)
	{
		$this['events']->listen('bootstrapped: '.$bootstrapper, $callback);
	}

	/**
	 * Determine if the application has been bootstrapped before.
	 *
	 * @return bool
	 */
	public function hasBeenBootstrapped()
	{
		return $this->hasBeenBootstrapped;
	}

	/**
	 * Set the base path for the application.
	 * 重新设置app目录
	 * @param  string  $basePath
	 * @return $this
	 */
	public function setBasePath($basePath)
	{
		$this->basePath = $basePath;
		$this->bindPathsInContainer();
		return $this;
	}

	/**
	 * Bind all of the application paths in the container.
	 *
	 * @return void
	 */
	protected function bindPathsInContainer()
	{
		$this->instance('path', $this->path());
		foreach (['base', 'config', 'database', 'lang', 'public', 'storage'] as $path)
		{
			$this->instance('path.'.$path, $this->{$path.'Path'}());
		}
	}

	/**
	 * Get the path to the application "app" directory.
	 *
	 * @return string
	 */
	public function path()
	{
		return $this->basePath.DIRECTORY_SEPARATOR.'app';
	}

	/**
	 * Get the base path of the Laravel installation.
	 *
	 * @return string
	 */
	public function basePath()
	{
		return $this->basePath;
	}

	/**
	 * Get the path to the application configuration files.
	 *
	 * @return string
	 */
	public function configPath()
	{
		return $this->basePath.DIRECTORY_SEPARATOR.'config';
	}

	/**
	 * Get the path to the database directory.
	 *
	 * @return string
	 */
	public function databasePath()
	{
		return $this->basePath.DIRECTORY_SEPARATOR.'database';
	}

	/**
	 * Get the path to the language files.
	 *
	 * @return string
	 */
	public function langPath()
	{
		return $this->basePath.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'lang';
	}

	/**
	 * Get the path to the public / web directory.
	 *
	 * @return string
	 */
	public function publicPath()
	{
		return $this->basePath.DIRECTORY_SEPARATOR.'public';
	}

	/**
	 * Get the path to the storage directory.
	 *
	 * @return string
	 */
	public function storagePath()
	{
		return $this->storagePath ?: $this->basePath.DIRECTORY_SEPARATOR.'storage';
	}

	/**
	 * Set the storage directory.
	 *
	 * @param  string  $path
	 * @return $this
	 */
	public function useStoragePath($path)
	{
		$this->storagePath = $path;
		$this->instance('path.storage', $path);
		return $this;
	}

	/**
	 * Set the environment file to be loaded during bootstrapping.
	 * 设置.env文件
	 * @param  string  $file
	 * @return $this
	 */
	public function loadEnvironmentFrom($file)
	{
		$this->environmentFile = $file;

		return $this;
	}

	/**
	 * Get the environment file the application is using.
	 * 获取.env文件
	 * @return string
	 */
	public function environmentFile()
	{
		return $this->environmentFile ?: '.env';
	}

	/**
	 * Get or check the current application environment.
	 *
	 * @param  mixed
	 * @return string
	 */
	public function environment()
	{
		if (func_num_args() > 0)
		{
			$patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

			foreach ($patterns as $pattern)
			{
				if (str_is($pattern, $this['env']))
				{
					return true;
				}
			}

			return false;
		}

		return $this['env'];
	}

	/**
	 * Determine if application is in local environment.
	 *
	 * @return bool
	 */
	public function isLocal()
	{
		return $this['env'] == 'local';
	}

	/**
	 * Detect the application's current environment.
	 *
	 * @param  \Closure  $callback
	 * @return string
	 */
	public function detectEnvironment(Closure $callback)
	{
		$args = isset($_SERVER['argv']) ? $_SERVER['argv'] : null;//--env=dev 取到dev值

		return $this['env'] = (new EnvironmentDetector())->detect($callback, $args);
	}

	/**
	 * Determine if we are running in the console.
	 *
	 * @return bool
	 */
	public function runningInConsole()
	{
		return php_sapi_name() == 'cli';
	}

	/**
	 * Determine if we are running unit tests.
	 *
	 * @return bool
	 */
	public function runningUnitTests()
	{
		return $this['env'] == 'testing';
	}

	/**
	 * Register all of the configured providers.
	 *
	 * @return void
	 */
	public function registerConfiguredProviders()
	{
		$manifestPath = $this->basePath().'/vendor/services.json';

		(new ProviderRepository($this, new Filesystem, $manifestPath))
		            ->load($this->config['app.providers']);
	}

	/**
	 * Register a service provider with the application.
	 *
	 * @param  $provider = 服务提供者对象或者服务提供者类名
	 * @param  array  $options 设置bindings[$key]属性值
	 * @param  bool   $force 是否强制重新执行regiser（）
	 * @return \Illuminate\Support\ServiceProvider
	 */
	public function register($provider, $options = array(), $force = false)
	{	//服务提供者已经实例化过则直接返回对象
		if ($registered = $this->getProvider($provider) && ! $force)
             return $registered;

		if (is_string($provider))
		{//是字符串则实例化服务提供者类，并注入app对象
			$provider = $this->resolveProviderClass($provider);//是new $provider($this);这样代码
		}
		#调用服务提供者类的register()方法
		$provider->register();

		foreach ($options as $key => $value)
		{	#调用app对象的offsetSet($key, $value)方法=》app对象->bind($key, $value, false); =>设置bindings[$key]属性值
			$this[$key] = $value;
		}
		//把已经实例化服务提供者对象存入属性$serviceProviders[]=$provider，$loadedProviders[provider类名]=true
		$this->markAsRegistered($provider);


		if ($this->booted)
		{//调用$provider服务提供者类对象的boot()方法
			$this->bootProvider($provider);
		}
		//返回服务提供者对象
		return $provider;
	}

	/**
	 * Get the registered service provider instance if it exists.
	 * 通过服务提供者类对象或类名在serviceProviders数组中 来获取服务提供者对象,不存在返回null
	 * @param  \Illuminate\Support\ServiceProvider|string  $provider服务提供者对象或者服务提供者类名
	 * @return \Illuminate\Support\ServiceProvider|null
	 */
	public function getProvider($provider)
	{
		$name = is_string($provider) ? $provider : get_class($provider);//服务提供者类名
		#函数返回数组中第一个通过给定的array_first第2个参数测试为真的元素， 不存在则返回null
		return array_first($this->serviceProviders, function($key, $value) use ($name)
		{// 对象 instanceof 类名
			return $value instanceof $name;
		});
	}

	/**
	 * Resolve a service provider instance from the class name.
	 * 实例化服务提供者
	 * @param  string  $provider
	 * @return \Illuminate\Support\ServiceProvider
	 */
	public function resolveProviderClass($provider)
	{
		return new $provider($this);
	}

	/**
	 * Mark the given provider as registered.
	 * 设置serviceProviders和loadedProviders属性归档
	 * @param  \Illuminate\Support\ServiceProvider 服务提供者对象
	 * @return void
	 */
	protected function markAsRegistered($provider)
	{
		$this['events']->fire($class = get_class($provider), array($provider));

		$this->serviceProviders[] = $provider;//把已经实例化的服务提供者对象存入serviceProviders属性

		$this->loadedProviders[$class] = true;//标记已经实例化
	}

	/**
	 * Load and boot all of the remaining deferred providers.
	 *
	 * @return void
	 */
	public function loadDeferredProviders()
	{
		// We will simply spin through each of the deferred providers and register each
		// one and boot them if the application has booted. This should make each of
		// the remaining services available to this application for immediate use.
		foreach ($this->deferredServices as $service => $provider)
		{
			$this->loadDeferredProvider($service);
		}

		$this->deferredServices = array();
	}

	/**
	 * Load the provider for a deferred service.
	 * 执行服务提供者的register()方法
	 * @param  string  $service
	 * @return void
	 */
	public function loadDeferredProvider($service)
	{
		if ( ! isset($this->deferredServices[$service]))
		{
			return;
		}

		$provider = $this->deferredServices[$service];
		if ( ! isset($this->loadedProviders[$provider]))
		{//未实例化 则执行服务提供者的register()方法
			$this->registerDeferredProvider($provider, $service);
		}
	}

	/**
	 * Register a deferred provider and service.
	 *
	 * @param  string  $provider
	 * @param  string  $service
	 * @return void
	 */
	public function registerDeferredProvider($provider, $service = null)
	{
		if ($service) unset($this->deferredServices[$service]); //从延迟中移除

		$this->register($instance = new $provider($this));//执行服务提供者的register()方法

		if ( ! $this->booted)
		{
			$this->booting(function() use ($instance)
			{
				$this->bootProvider($instance);//执行服务提供者的boot方法
			});
		}
	}

	/**
	 * Resolve the given type from the container.
	 *
	 * (Overriding Container::make)
	 *
	 * @param  string  $abstract
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function make($abstract, $parameters = array())
	{
		//返回真实的$abstract
		$abstract = $this->getAlias($abstract);// $this->aliases[$abstract]  || $abstract

		if (isset($this->deferredServices[$abstract]))
		{//有延迟的服务提供者
			$this->loadDeferredProvider($abstract);//执行服务提供者的register()方法
		}

		return parent::make($abstract, $parameters);
	}

	/**
	 * Determine if the given abstract type has been bound.
	 *
	 * (Overriding Container::bound)
	 *
	 * @param  string  $abstract
	 * @return bool
	 */
	public function bound($abstract)
	{
		return isset($this->deferredServices[$abstract]) || parent::bound($abstract);
	}

	/**
	 * Determine if the application has booted.
	 *
	 * @return bool
	 */
	public function isBooted()
	{
		return $this->booted;
	}

	/**
	 * Boot the application's service providers.
	 *
	 * @return void
	 */
	public function boot()
	{
		if ($this->booted) return;
		$this->fireAppCallbacks($this->bootingCallbacks);
		//serviceProviders属性存已经实例化的服务提供者对象,
		array_walk($this->serviceProviders, function($p) {#执行所有服务提供者的boot方法
			$this->bootProvider($p);
		});

		$this->booted = true;

		$this->fireAppCallbacks($this->bootedCallbacks);
	}

	/**
	 * Boot the given service provider.
	 * 存在boot方法则调用
	 * @param  \Illuminate\Support\ServiceProvider  $provider=服务提供者类对象
	 * @return void
	 */
	protected function bootProvider(ServiceProvider $provider)
	{
		if (method_exists($provider, 'boot'))
		{//调用服务类对象的boot方法
			return $this->call([$provider, 'boot']);
		}
	}

	/**
	 * Register a new boot listener.
	 *
	 * @param  mixed  $callback
	 * @return void
	 */
	public function booting($callback)
	{
		$this->bootingCallbacks[] = $callback;
	}

	/**
	 * Register a new "booted" listener.
	 *
	 * @param  mixed  $callback
	 * @return void
	 */
	public function booted($callback)
	{
		$this->bootedCallbacks[] = $callback;

		if ($this->isBooted()) $this->fireAppCallbacks(array($callback));
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(SymfonyRequest $request, $type = self::MASTER_REQUEST, $catch = true)
	{
		return $this['Illuminate\Contracts\Http\Kernel']->handle(Request::createFromBase($request));
	}

	/**
	 * Determine if the application configuration is cached.
	 *
	 * @return bool
	 */
	public function configurationIsCached()
	{
		return $this['files']->exists($this->getCachedConfigPath());
	}

	/**
	 * Get the path to the configuration cache file.
	 *
	 * @return string
	 */
	public function getCachedConfigPath()
	{
		return $this['path.storage'].DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'config.php';
	}

	/**
	 * Determine if the application routes are cached.
	 *
	 * @return bool
	 */
	public function routesAreCached()
	{
		return $this['files']->exists($this->getCachedRoutesPath());
	}

	/**
	 * Get the path to the routes cache file.
	 *
	 * @return string
	 */
	public function getCachedRoutesPath()
	{
		return $this->basePath().'/vendor/routes.php';
	}

	/**
	 * Call the booting callbacks for the application.
	 *
	 * @param  array  $callbacks
	 * @return void
	 */
	protected function fireAppCallbacks(array $callbacks)
	{
		foreach ($callbacks as $callback)
		{
			call_user_func($callback, $this);
		}
	}

	/**
	 * Determine if the application is currently down for maintenance.
	 *
	 * @return bool
	 */
	public function isDownForMaintenance()
	{
		return file_exists($this->storagePath().DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'down');
	}

	/**
	 * Register a maintenance mode event listener.
	 *
	 * @param  \Closure  $callback
	 * @return void
	 */
	public function down(Closure $callback)
	{
		$this['events']->listen('illuminate.app.down', $callback);
	}

	/**
	 * Throw an HttpException with the given data.
	 *
	 * @param  int     $code
	 * @param  string  $message
	 * @param  array   $headers
	 * @return void
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function abort($code, $message = '', array $headers = array())
	{
		if ($code == 404)
		{
			throw new NotFoundHttpException($message);
		}

		throw new HttpException($code, $message, null, $headers);
	}

	/**
	 * Register a terminating callback with the application.
	 *
	 * @param  \Closure  $callback
	 * @return $this
	 */
	public function terminating(Closure $callback)
	{
		$this->terminatingCallbacks[] = $callback;

		return $this;
	}

	/**
	 * Terminate the application.
	 *
	 * @return void
	 */
	public function terminate()
	{
		foreach ($this->terminatingCallbacks as $terminating)
		{
			$this->call($terminating);
		}
	}

	/**
	 * Get the service providers that have been loaded.
	 *
	 * @return array
	 */
	public function getLoadedProviders()
	{
		return $this->loadedProviders;
	}

	/**
	 * Set the application's deferred services.
	 *
	 * @param  array  $services
	 * @return void
	 */
	public function setDeferredServices(array $services)
	{
		$this->deferredServices = $services;
	}

	/**
	 * Determine if the given service is a deferred service.
	 *
	 * @param  string  $service
	 * @return bool
	 */
	public function isDeferredService($service)
	{
		return isset($this->deferredServices[$service]);
	}

	/**
	 * Get the current application locale.
	 *
	 * @return string
	 */
	public function getLocale()
	{
		return $this['config']->get('app.locale');
	}

	/**
	 * Set the current application locale.
	 *
	 * @param  string  $locale
	 * @return void
	 */
	public function setLocale($locale)
	{
		$this['config']->set('app.locale', $locale);

		$this['translator']->setLocale($locale);

		$this['events']->fire('locale.changed', array($locale));
	}

	/**
	 * Register the core class aliases in the container.
	 *
	 * @return void
	 */
	public function registerCoreContainerAliases()
	{
		$aliases = array(
			'app'                  => ['Illuminate\Foundation\Application', 'Illuminate\Contracts\Container\Container', 'Illuminate\Contracts\Foundation\Application'],
			'artisan'              => ['Illuminate\Console\Application', 'Illuminate\Contracts\Console\Application'],
			'auth'                 => 'Illuminate\Auth\AuthManager',
			'auth.driver'          => ['Illuminate\Auth\Guard', 'Illuminate\Contracts\Auth\Guard'],
			'auth.password.tokens' => 'Illuminate\Auth\Passwords\TokenRepositoryInterface',
			'blade.compiler'       => 'Illuminate\View\Compilers\BladeCompiler',
			'cache'                => ['Illuminate\Cache\CacheManager', 'Illuminate\Contracts\Cache\Factory'],
			'cache.store'          => ['Illuminate\Cache\Repository', 'Illuminate\Contracts\Cache\Repository'],
			'config'               => ['Illuminate\Config\Repository', 'Illuminate\Contracts\Config\Repository'],
			'cookie'               => ['Illuminate\Cookie\CookieJar', 'Illuminate\Contracts\Cookie\Factory', 'Illuminate\Contracts\Cookie\QueueingFactory'],
			'encrypter'            => ['Illuminate\Encryption\Encrypter', 'Illuminate\Contracts\Encryption\Encrypter'],
			'db'                   => 'Illuminate\Database\DatabaseManager',
			'events'               => ['Illuminate\Events\Dispatcher', 'Illuminate\Contracts\Events\Dispatcher'],
			'files'                => 'Illuminate\Filesystem\Filesystem',
			'filesystem'           => 'Illuminate\Contracts\Filesystem\Factory',
			'filesystem.disk'      => 'Illuminate\Contracts\Filesystem\Filesystem',
			'filesystem.cloud'     => 'Illuminate\Contracts\Filesystem\Cloud',
			'hash'                 => 'Illuminate\Contracts\Hashing\Hasher',
			'translator'           => ['Illuminate\Translation\Translator', 'Symfony\Component\Translation\TranslatorInterface'],
			'log'                  => ['Illuminate\Log\Writer', 'Illuminate\Contracts\Logging\Log', 'Psr\Log\LoggerInterface'],
			'mailer'               => ['Illuminate\Mail\Mailer', 'Illuminate\Contracts\Mail\Mailer', 'Illuminate\Contracts\Mail\MailQueue'],
			'paginator'            => 'Illuminate\Pagination\Factory',
			'auth.password'        => ['Illuminate\Auth\Passwords\PasswordBroker', 'Illuminate\Contracts\Auth\PasswordBroker'],
			'queue'                => ['Illuminate\Queue\QueueManager', 'Illuminate\Contracts\Queue\Factory', 'Illuminate\Contracts\Queue\Monitor'],
			'queue.connection'     => 'Illuminate\Contracts\Queue\Queue',
			'redirect'             => 'Illuminate\Routing\Redirector',
			'redis'                => ['Illuminate\Redis\Database', 'Illuminate\Contracts\Redis\Database'],
			'request'              => 'Illuminate\Http\Request',
			'router'               => ['Illuminate\Routing\Router', 'Illuminate\Contracts\Routing\Registrar'],
			'session'              => 'Illuminate\Session\SessionManager',
			'session.store'        => ['Illuminate\Session\Store', 'Symfony\Component\HttpFoundation\Session\SessionInterface'],
			'url'                  => ['Illuminate\Routing\UrlGenerator', 'Illuminate\Contracts\Routing\UrlGenerator'],
			'validator'            => ['Illuminate\Validation\Factory', 'Illuminate\Contracts\Validation\Factory'],
			'view'                 => ['Illuminate\View\Factory', 'Illuminate\Contracts\View\Factory'],
		);

		foreach ($aliases as $key => $aliases)
		{
			foreach ((array) $aliases as $alias)
			{
				$this->alias($key, $alias);
			}
		}
	}

	/**
	 * Flush the container of all bindings and resolved instances.
	 *
	 * @return void
	 */
	public function flush()
	{
		parent::flush();

		$this->loadedProviders = [];
	}

}
