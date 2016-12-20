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
		}
		catch (InvalidArgumentException $e)
		{
			//
		}
        //设置当前环境
		$app->detectEnvironment(function()
		{
			return env('APP_ENV', 'production');
		});
	}

}
