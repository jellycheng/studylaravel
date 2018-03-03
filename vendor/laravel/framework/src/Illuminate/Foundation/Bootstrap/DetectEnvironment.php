<?php namespace Illuminate\Foundation\Bootstrap;

use Dotenv;
use InvalidArgumentException;
use Illuminate\Contracts\Foundation\Application;

class DetectEnvironment {

	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		try
		{   //分析.env文件
			Dotenv::load($app['path.base'], $app->environmentFile());
		} catch (InvalidArgumentException $e) {
			//
		}

		$app->detectEnvironment(function()
		{//设置当前环境值,注入app对象['env']=APP_ENV值
			return env('APP_ENV', 'production');//返回.env中配置的APP_ENV值
		});
	}

}
