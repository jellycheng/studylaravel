<?php namespace Illuminate\Foundation\Bootstrap;

use Illuminate\Support\Facades\Facade;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Contracts\Foundation\Application;

class RegisterFacades {

	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		Facade::clearResolvedInstances();
		Facade::setFacadeApplication($app);
		//设置类别名，且设置自动加载器
		AliasLoader::getInstance($app['config']['app.aliases'])->register();
	}

}
