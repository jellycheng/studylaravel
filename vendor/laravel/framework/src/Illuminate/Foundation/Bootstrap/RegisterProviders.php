<?php namespace Illuminate\Foundation\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class RegisterProviders {

	/**
	 * Bootstrap the given application.
	 * 调用app对象->registerConfiguredProviders();即执行服务提供者的register()方法;
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		$app->registerConfiguredProviders();
	}

}
