<?php namespace Illuminate\Foundation\Bootstrap;

use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;

class SetRequestForConsole {

	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		$url = $app['config']->get('app.url', 'http://localhost');//获取应用程序url地址

		$app->instance('request', Request::create($url, 'GET', [], [], [], $_SERVER));//容器中设置请求对象
	}

}
