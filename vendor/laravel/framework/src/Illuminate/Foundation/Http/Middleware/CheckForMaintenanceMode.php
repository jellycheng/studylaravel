<?php namespace Illuminate\Foundation\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\Middleware; //路由中间介接口
use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckForMaintenanceMode implements Middleware {

	/**
	 * The application implementation.
	 *
	 * @var \Illuminate\Contracts\Foundation\Application
	 */
	protected $app;

	/**
	 * Create a new filter instance.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request 请求对象
	 * @param  \Closure  $next 闭包
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($this->app->isDownForMaintenance())
		{//判断是否在维护中
			throw new HttpException(503);
		}

		return $next($request);
	}

}
