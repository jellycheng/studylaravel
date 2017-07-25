<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
//自定义的服务器提供者，如果要在启动代码流程中执行则配置/config/app.php文件的'providers'=>['其它服务器提供者', 'App\Providers\JellyServiceProvider']
class JellyServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//单例
		$this->app->singleton('jellyImage', function ($app) {
			return new \App\Lib\JellyImage();
		});

	}

}
