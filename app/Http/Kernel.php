<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 * http中间介,且类均实现handle()方法, 一般情况,新项目把这些中间介不配置
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode', //监测项目是否在维护模式，维护模式抛httpexception异常
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',
		//'App\Http\Middleware\VerifyCsrfToken',
	];

	/**
	 * The application's route middleware.
	 * 路由中间介，会传递给router类对象的middleware属性中(在实例化本类时注入Router类中)
	 * 一般新的项目不配置这个属性,把该属性值变为空数组,如何根据业务再自行配置路由中间介
	 * 路由中间介类只有被调用或者匹配到对应的路由时才会实例化执行:
	 *  方式1: 控制器中$this->middleware('路由中间介名');
	 * 	方式2: 在路由中配置:
	 * 		  Route::group(['middleware'=>['中间介1', '中间介N'], 'prefix' => 'vshop', 'namespace' => 'Vshop'], function () { });
	 *        Route::get('urlxxx/profile', ['middleware' =>'auth','uses'=>'UserController@showProfile']);
	 * @var array
	 */
	protected $routeMiddleware = [
		//中间介名=>中间介类名,  #中间介类均有handle()方法,只有在这里配置了,才可以在其它地方调用
		'auth' => 'App\Http\Middleware\Authenticate',
		'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
		'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',

		'cors'=>'App\Http\Middleware\Cors',
	];

}
