<?php namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider {

	/**
	 * This namespace is applied to the controller routes in your routes file.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'App\Http\Controllers';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @param  \Illuminate\Routing\Router  $router 路由管理者
	 * @return void
	 */
	public function boot(Router $router)
	{
		parent::boot($router);

		//
	}

	/**
	 * Define the routes for the application.
	 *
	 * @param  \Illuminate\Routing\Router  $router 路由管理者
	 * @return void
	 */
	public function map(Router $router)
	{
		//设置路由
		$router->group(['namespace' => $this->namespace], function($router)
		{//控制器类均是在App\Http\Controllers\目录下
			require app_path('Http/routes.php'); //加载路由配置
		});

		//扩展路由
		foreach (config('modules.list', []) as $dir => $module) {
			//设置路由
			$router->group(
				[
					'namespace' => 'App\Modules\\' . $dir . '\Http\Controllers', //控制器命名空间
					'prefix' => strtolower($module), //url前缀
				],
				function ($router) use ($dir) {
					//app/Modules/项目名/Http/routes.php
					require app_path('Modules/' . $dir . '/Http/routes.php');//加载路由配置文件
				}
			);
		}

	}

}
