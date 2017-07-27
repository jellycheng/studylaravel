<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//监听sql执行，在Illuminate\Database\Connection.php文件的logQuery方法触法事件，listen方法可以监听事件
        app('events')->listen('illuminate.query', function ($sql, $binds, $time, $dbname) {
            /**
            \Log::info(
                [
                    'sql' => $sql,
                    'time' => $time,
                    'binds' => $binds,
                    'dbname' => $dbname
                ]);
            */
            \Log::info(sprintf('db_connection:%s,sql: %s param:%s',$dbname, var_export($sql, true), var_export($binds, true)));
        });

        //监听方式2：与上面一种方式不同点是：不论是否执行sql，每次请求均连接Db
//          \DB::connection('mysql')->listen(
//                           function ($sql, $binds, $time, $connectionName) {
//                              \Log::info(
//                              [
//                              'sql'   => $sql,
//                              'time'  => $time,
//                              'binds' => $binds,
//                              'connection'=>$connectionName,
//                              ]);
//                          });
	}

	/**
	 * Register any application services.
	 *
	 * This service provider is a great spot to register your various container
	 * bindings with the application. As you can see, we are registering our
	 * "Registrar" implementation here. You can add your own bindings too!
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind(
			'Illuminate\Contracts\Auth\Registrar',
			'App\Services\Registrar'
		);
	}

}
