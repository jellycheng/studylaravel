<?php namespace Illuminate\Events;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{	//单例
		$this->app->singleton('events', function($app)
		{//返回Illuminate\Events\Dispatcher类对象
			return (new Dispatcher($app))->setQueueResolver(function() use ($app)
			{
				return $app->make('Illuminate\Contracts\Queue\Queue');
			});
		});
	}

}
