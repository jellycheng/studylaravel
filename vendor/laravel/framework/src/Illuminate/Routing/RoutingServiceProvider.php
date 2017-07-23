<?php namespace Illuminate\Routing;

use Illuminate\Support\ServiceProvider;
//路由服务提供者
class RoutingServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerRouter(); //设置Router类对象
		$this->registerUrlGenerator();//设置UrlGenerator类对象
		$this->registerRedirector();//设置Redirector类对象
		$this->registerResponseFactory();//注册一个响应工厂
	}

	/**
	 * Register the router instance.
	 * 注册路由实例
	 * @return void
	 */
	protected function registerRouter()
	{
		$this->app['router'] = $this->app->share(function($app)
		{
			return new Router($app['events'], $app);
		});
	}

	/**
	 * Register the URL generator service.
	 *
	 * @return void
	 */
	protected function registerUrlGenerator()
	{
		$this->app['url'] = $this->app->share(function($app)
		{
			$routes = $app['router']->getRoutes();//获取RouteCollection类对象即路由集合对象
			$app->instance('routes', $routes);
			$url = new UrlGenerator(
								$routes,
								$app->rebinding(
									'request', $this->requestRebinder()
								)
							);
			//设置session解决者
			$url->setSessionResolver(function()
			{
				return $this->app['session'];
			});
			//
			$app->rebinding('routes', function($app, $routes) {
				$app['url']->setRoutes($routes);
			});
			return $url;
		});
	}

	/**
	 * @return \Closure  返回闭包(app对象, 请求对象即抽象物对象即实现物对象)
	 */
	protected function requestRebinder()
	{
		return function($app, $request)
		{
			$app['url']->setRequest($request);
		};
	}

	/**
	 * Register the Redirector service.
	 *
	 * @return void
	 */
	protected function registerRedirector()
	{
		$this->app['redirect'] = $this->app->share(function($app)
		{
			$redirector = new Redirector($app['url']);
			//
			if (isset($app['session.store']))
			{
				$redirector->setSession($app['session.store']);
			}
			return $redirector;
		});
	}

	/**
	 * Register the response factory implementation.注册一个响应工厂
	 * @return void
	 */
	protected function registerResponseFactory()
	{
		$this->app->singleton('Illuminate\Contracts\Routing\ResponseFactory', function($app)
		{
			return new ResponseFactory($app['Illuminate\Contracts\View\Factory'], $app['redirect']);
		});
	}

}
