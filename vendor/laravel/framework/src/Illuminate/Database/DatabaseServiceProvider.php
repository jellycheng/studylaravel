<?php namespace Illuminate\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connectors\ConnectionFactory;

class DatabaseServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		//向Model类注入连接管理类对象: \Illuminate\Database\DatabaseManager 类对象
		Model::setConnectionResolver($this->app['db']);
		//向Model类注入事件对象: Illuminate\Events\Dispatcher类对象
		Model::setEventDispatcher($this->app['events']);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerQueueableEntityResolver();

		//Illuminate\Database\Connectors\ConnectionFactory类对象
		$this->app->singleton('db.factory', function($app)
		{
			return new ConnectionFactory($app);
		});

		//DB facade就是\Illuminate\Database\DatabaseManager类对象
		$this->app->singleton('db', function($app)
		{
			return new DatabaseManager($app, $app['db.factory']);
		});
	}

	/**
	 * Register the queueable entity resolver implementation.
	 *
	 * @return void
	 */
	protected function registerQueueableEntityResolver()
	{
		$this->app->singleton('Illuminate\Contracts\Queue\EntityResolver', function()
		{
			return new Eloquent\QueueEntityResolver;
		});
	}

}
