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
	 * @var string
	 */
	const VERSION = '5.0.16';

	/**
	 * The base path for the Laravel installation.
	 * 项目目录
	 * @var string
	 */
	protected $basePath;

	/**
	 * Indicates if the application has been bootstrapped before.
	 * 是否批量执行$bootstrapper对象的bootstrap(app对象)方法即本类的bootstrapWith()方法执行完毕
	 * 即把在Illuminate\Foundation\Http\Kernel类bootstrappers属性中配置的类名执行完毕了
	 * @var bool
	 */
	protected $hasBeenBootstrapped = false;

	/**
	 * Indicates if the application has "booted".
	 * 是否执行过app的boot()方法
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
	 * 存已经实例化且被执行过register()方法的服务提供者对象
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
	 * storage目录，一般是项目目录/storage， 可以通过useStoragePath(目录)方法改变
	 * @var string
	 */
	protected $storagePath;

	/**
	 * The environment file to load during bootstrapping.
	 * env文件名
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
	    //1.注册基本绑定，设置本类对象
		$this->registerBaseBindings();
		//2.注册服务提供者： 1.事件服务提供者，2.路由服务提供者   备注:服务提供者构造方法接收laravel app对象
		$this->registerBaseServiceProviders();
		//3.在容器中注册类的核心别名
		$this->registerCoreContainerAliases();
		//4.设置app类basePath属性和instance属性[path.项目目录代号]=目录,方便后面通过本类app对象->basePath()获取的是$basePath值
		if ($basePath) $this->setBasePath($basePath);
	}

	/**
	 * Get the version number of the application.
	 * 获取laravel app版本
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
        //设置instances属性[key]=值
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
	 * Run the given array of bootstrap classes.在http类的bootstrap()方法中调用这个方法
	 * 批量执行$bootstrapper对象->bootstrap(app对象);
	 * bootstrappers对象是在Illuminate\Foundation\Http\Kernel类bootstrappers属性中配置,也可以在App\Http\Kernel类重写属性
	 * @param  array  $bootstrappers=[$bootstrapper1类名or抽象物or别名, $bootstrapper2,$bootstrapperN]
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
		//标记app启动完毕
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
		$this->basePath = $basePath; //项目目录
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
		$this->instance('path', $this->path()); //app目录,获取该值方式:1.app对象->make('path');2.$app对象['path'];3.app('path');4.app_path();
		foreach (['base', 'config', 'database', 'lang', 'public', 'storage'] as $path)
		{   //path.base=项目代码根目录
			//path=项目app目录
            //path.config=项目代码根目录/config
			$this->instance('path.'.$path, $this->{$path.'Path'}());
		}
	}

	/**
	 * Get the path to the application "app" directory.
	 * 代码app目录
	 * @return string
	 */
	public function path()
	{
		return $this->basePath.DIRECTORY_SEPARATOR.'app';
	}

	/**
	 * Get the base path of the Laravel installation.
	 * 项目代码根目录
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
	 * storage目录，一般是项目目录/storage
	 * @return string
	 */
	public function storagePath()
	{
		return $this->storagePath ?: $this->basePath.DIRECTORY_SEPARATOR.'storage';
	}

	/**
	 * Set the storage directory.
	 * 重新设置storage目录，一般是项目目录/storage
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
	 * 设置.env文件名
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
	 * 获取.env文件名
	 * @return string
	 */
	public function environmentFile()
	{
		return $this->environmentFile ?: '.env';
	}

	/**
	 * Get or check the current application environment.
	 * app对象->environment()返回所有环境
     * app对象->environment('环境代号1');返回是否存在环境代号1
     * app对象->environment('环境代号1','环境代号2', '环境代号N');返回是否存在环境代号1或2或N，其中的一个
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
	 * 设置当前env环境值， 如果cli方式则先分析参数中是否存在--env=环境代号，没有则通过回调闭包函数获取环境代号
	 * @param  \Closure  $callback  闭包，如果在cli参数中分析不出环境则调用闭包获取环境,提供给call_user_func（$callback）调用
	 * @return string
	 */
	public function detectEnvironment(Closure $callback)
	{
		$args = isset($_SERVER['argv']) ? $_SERVER['argv'] : null;//有值则是cli方式,如--env=dev 无值则是http方式

		return $this['env'] = (new EnvironmentDetector())->detect($callback, $args);
	}

	/**
	 * Determine if we are running in the console.
	 * 是否cli执行
	 * @return bool
	 */
	public function runningInConsole()
	{
		return php_sapi_name() == 'cli';
	}

	/**
	 * Determine if we are running unit tests.
	 * 环境是否为执行单元测试
	 * @return bool
	 */
	public function runningUnitTests()
	{
		return $this['env'] == 'testing';
	}

	/**
	 * Register all of the configured providers.
	 * 把服务提供者类cache到指定json文件中,并执行register()方法
	 * @return void
	 */
	public function registerConfiguredProviders()
	{
		$manifestPath = $this->basePath().'/vendor/services.json';
		//有的laravel框架版本，这个缓存文件是在 $manifestPath = $this->storagePath().DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'services.json';
		(new ProviderRepository($this, new Filesystem, $manifestPath))
		            ->load($this->config['app.providers']);
	}

	/**
	 * Register a service provider with the application.
	 * 注册服务提供者并执行其register()方法
	 * @param  $provider = 服务提供者类对象 或者 服务提供者类名
	 * @param  array  $options = [key=>val] 设置app类对象的bindings[key]=val
	 * @param  bool   $force 是否强制重新执行regiser()
	 * @return \Illuminate\Support\ServiceProvider
	 */
	public function register($provider, $options = array(), $force = false)
	{	//服务提供者已实例化且执行过register()方法则返回true
		if ($registered = $this->getProvider($provider) && ! $force)
             return $registered;

		if (is_string($provider))
		{//是字符串则实例化服务提供者类，并注入app类对象
			$provider = $this->resolveProviderClass($provider);//是new $provider($this);这样代码
		}
		#调用服务提供者类的register()方法
		$provider->register();

		foreach ($options as $key => $value)
		{	#调用app对象->offsetSet($key, $value)方法;=》app对象->bind($key, $value, false); =>设置bindings[$key]属性=$value值
			$this[$key] = $value;
		}
		//把已经实例化服务提供者对象存入app类属性$serviceProviders[]=$provider，$loadedProviders[provider类名]=true
		$this->markAsRegistered($provider);

		if ($this->booted)
		{//调用过app对象的boot()方法，则调用$provider服务提供者类对象的boot()方法
			$this->bootProvider($provider);
		}
		//返回服务提供者对象
		return $provider;
	}

	/**
	 * Get the registered service provider instance if it exists.
	 * 服务提供者类对象(或类名)在Application类的serviceProviders属性中能找到对象,存放返回true,不存在返回null
	 * @param  \Illuminate\Support\ServiceProvider|string  $provider服务提供者类对象 或者 服务提供者类名
	 * @return \Illuminate\Support\ServiceProvider|null 已经是服务器提供者返回对象，否则返回nulll
	 */
	public function getProvider($provider)
	{
		$name = is_string($provider) ? $provider : get_class($provider);//服务提供者类名
		#遍历app对象的serviceProviders属性,如果值是$provider服务提供者类对象则返回真， 不存在则返回null
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
	 * @param  \Illuminate\Support\ServiceProvider 服务提供者类对象
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
	 * @param  string  $provider 服务提供者类
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
	 * 覆盖了父类的make方法
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
	 * 一个app对象该方法只会执行一次
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
	 * 服务提供者类存在boot方法则调用其boot方法
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
	 * 判断storage/framwwork/config.php配置文件是否存在
	 * @return bool
	 */
	public function configurationIsCached()
	{
		return $this['files']->exists($this->getCachedConfigPath());
	}

	/**
	 * Get the path to the configuration cache file.
	 * 获取storage/framwwork/config.php配置文件
	 * @return string
	 */
	public function getCachedConfigPath()
	{
		return $this['path.storage'].DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'config.php';
	}

	/**
	 * Determine if the application routes are cached.
	 * 判断缓存路由文件是否存在
	 * @return bool
	 */
	public function routesAreCached()
	{
		return $this['files']->exists($this->getCachedRoutesPath());
	}

	/**
	 * Get the path to the routes cache file.
	 * 路由缓存文件
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
	 * 维护模式响应的默认模板放在 resources/views/errors/503.blade.php
	 * 监测项目是否在维护模式： 判断storage/framework/down文件是否存在，存在则是维护模式
     * 可以使用命令 php artisan down 开启维护模式
     * 使用命令 php artisan up 关闭维护模式
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
	 * 抛异常
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
	 * 注册terminating回调
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
	 * 获取加载的服务提供者
	 * @return array
	 */
	public function getLoadedProviders()
	{
		return $this->loadedProviders;
	}

	/**
	 * Set the application's deferred services.
	 *
	 * @param  array  $services = [服务代号=>'', 服务代号2=>'',]
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
	 * 获取config/app.php中locale配置key对应的值
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
	 * 在容器中注册类的核心别名
	 * 用途: 使app对象->make('app');等价app对象->make('Illuminate\Foundation\Application');等价app对象->make('Illuminate\Contracts\Container\Container');等价app对象->make('Illuminate\Contracts\Foundation\Application')
	 * @return void
	 */
	public function registerCoreContainerAliases()
	{
		$aliases = array(
		    //'类代号（抽象物即make()方法的$abstract参数')=>['别名1即aliases属性的key名', '具体实现类,抽象类,接口']
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
			{   //$key=view抽象物, $alias=Illuminate\View\Factory别名
                //=>$this->aliases['Illuminate\View\Factory别名'] = 'view抽象物';
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
