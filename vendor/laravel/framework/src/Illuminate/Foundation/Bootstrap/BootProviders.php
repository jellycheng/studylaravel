<?php namespace Illuminate\Foundation\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class BootProviders {

	/**
	 * Bootstrap the given application.
	 * 执行app对象的boot()方法
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		$app->boot();
	}

}
