<?php namespace Illuminate\Foundation\Http;

use Exception;
use Illuminate\Routing\Router;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\TerminableMiddleware;
use Illuminate\Contracts\Http\Kernel as KernelContract;

class Kernel implements KernelContract {

	/**
	 * The application implementation. app对象
	 * @var \Illuminate\Contracts\Foundation\Application
	 */
	protected $app;

	/**
	 * The router instance.路由者对象
	 * @var \Illuminate\Routing\Router
	 */
	protected $router;

	/**
	 * The bootstrap classes for the application.
	 *  http启动时执行的类,且这些类均有bootstrap(app对象)方法和构造函数接收app对象
	 * @var array
	 */
	protected $bootstrappers = [
		'Illuminate\Foundation\Bootstrap\DetectEnvironment',//分析.env文件，并设置当前环境
		'Illuminate\Foundation\Bootstrap\LoadConfiguration',//加载config配置文件，设置时区,设置编码,可以使用$app['config']['app.aliases']获取配置值
		'Illuminate\Foundation\Bootstrap\ConfigureLogging',//设置日志,可通过app['log']获取日志对象,写日志app['log']->info("信息内容");等价Log::info('信息内容');
		'Illuminate\Foundation\Bootstrap\HandleExceptions',//异常handle设置,set_error_handler(),set_exception_handler(),register_shutdown_function()
		'Illuminate\Foundation\Bootstrap\RegisterFacades',//Facades类注入app对象，别名自动加载器,即把config/app.php中aliases配置的值定义好别名
		'Illuminate\Foundation\Bootstrap\RegisterProviders',//调用app对象->registerConfiguredProviders()，并执行服务提供者类的register()方法
		'Illuminate\Foundation\Bootstrap\BootProviders',//调用app对象->boot()方法（即执行所有服务提供者的boot()方法）
	];

	/**
	 * The application's middleware stack.
	 * http中间介,均实现handle()方法
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * The application's route middleware.
	 * 路由中间介
	 * @var array
	 */
	protected $routeMiddleware = [];

	/**
	 * Create a new HTTP kernel instance.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function __construct(Application $app, Router $router)
	{
		$this->app = $app;
		$this->router = $router;
		foreach ($this->routeMiddleware as $key => $middleware)
		{   //设置路由Router类对象的属性middleware（把在kernel中配置的中间介传给路由属性）=[中间介名=>中间介类]
			$router->middleware($key, $middleware);//$this->middleware[中间件名] = 类名;
		}
	}

	/**
	 * Handle an incoming HTTP request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function handle($request)
	{
		try
		{
			return $this->sendRequestThroughRouter($request);
		} catch (Exception $e) {
		    //上报异常log
			$this->reportException($e);
            //渲染异常页面
			return $this->renderException($request, $e);
		}
	}

	/**
	 * Send the given request through the middleware / router.
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	protected function sendRequestThroughRouter($request)
	{
		$this->app->instance('request', $request);
		Facade::clearResolvedInstance('request');//取消facade属性对象
		$this->bootstrap();//调用启动执行的类,且这些类均有bootstrap($app对象)方法
		//执行本类middleware属性设置的所有类->handle(请求对象,下一个闭包);最后执行dispatchToRouter()方法返回的闭包
		return (new Pipeline($this->app))
		            ->send($request)
		            ->through($this->middleware)
		            ->then($this->dispatchToRouter());
	}

	/**
	 * Call the terminate method on any terminable middleware.
	 *
	 * @param  \Illuminate\Http\Request  $request 请求对象
	 * @param  \Illuminate\Http\Response  $response 响应对象
	 * @return void
	 */
	public function terminate($request, $response)
	{
		$routeMiddlewares = $this->gatherRouteMiddlewares($request);

		foreach (array_merge($routeMiddlewares, $this->middleware) as $middleware)
		{
			$instance = $this->app->make($middleware);//获取terminate类型的中间介对象
			if ($instance instanceof TerminableMiddleware)
			{//http中间介和路由中间介中是terminate类型的中间介则执行其terminate()方法
				$instance->terminate($request, $response);
			}
		}

		$this->app->terminate();
	}

	/**
	 * Gather the route middleware for the given request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	protected function gatherRouteMiddlewares($request)
	{
		if ($request->route())
		{
			return $this->router->gatherRouteMiddlewares($request->route());
		}

		return [];
	}

	/**
	 * Add a new middleware to beginning of the stack if it does not already exist.
	 * 新增中间介
	 * @param  string  $middleware
	 * @return $this
	 */
	public function prependMiddleware($middleware)
	{
		if (array_search($middleware, $this->middleware) === false)
		{
			array_unshift($this->middleware, $middleware);
		}

		return $this;
	}

	/**
	 * Add a new middleware to end of the stack if it does not already exist.
	 * 新增中间介
	 * @param  string  $middleware
	 * @return $this
	 */
	public function pushMiddleware($middleware)
	{
		if (array_search($middleware, $this->middleware) === false)
		{
			$this->middleware[] = $middleware;
		}

		return $this;
	}

	/**
	 * Bootstrap the application for HTTP requests.
	 * 启动http
	 * @return void
	 */
	public function bootstrap()
	{
		if ( ! $this->app->hasBeenBootstrapped())
		{//app未启动执行完毕
			$this->app->bootstrapWith($this->bootstrappers());
		}
	}

	/**
	 * Get the route dispatcher callback.
	 *
	 * @return \Closure  返回闭包
	 */
	protected function dispatchToRouter()
	{
		return function($request)
		{
			$this->app->instance('request', $request);//注入请求对象
            //分析请求，路由
			return $this->router->dispatch($request);
		};
	}

	/**
	 * Get the bootstrap classes for the application.
	 *
	 * @return array
	 */
	protected function bootstrappers()
	{
		return $this->bootstrappers;
	}

	/**
	 * Report the exception to the exception handler.
	 * 上报异常log
	 * @param  \Exception  $e
	 * @return void
	 */
	protected function reportException(Exception $e)
	{
		$this->app['Illuminate\Contracts\Debug\ExceptionHandler']->report($e);
	}

	/**
	 * Render the exception to a response.
	 * 渲染异常结果
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function renderException($request, Exception $e)
	{
		return $this->app['Illuminate\Contracts\Debug\ExceptionHandler']->render($request, $e);
	}

	/**
	 * Get the Laravel application instance.
	 * 获取app对象，容器对象
	 * @return \Illuminate\Contracts\Foundation\Application
	 */
	public function getApplication()
	{
		return $this->app;
	}

}
