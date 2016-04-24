


服务提供者 类必须继承类： Illuminate\Support\ServiceProvider 该类的构造方法接收app对象
	服务提供者子类必须实现 register()方法

使用命令创建服务提供者子类：  php artisan make:provider JellyServiceProvider
此时生成文件app\Providers\JellyServiceProvider.php 文件内容如下：
	<?php namespace App\Providers;
	use Illuminate\Support\ServiceProvider;
	class JellyServiceProvider extends ServiceProvider {
		/**
		 * Bootstrap the application services.
		 * @return void
		 */
		public function boot()
		{
		   //这个方法可有可无，根据业务来写，父类的__call方法有实现调用不存在的boot方法时返回空： public function __call($method, $parameters){if ($method == 'boot') return;	throw new BadMethodCallException("Call to undefined method [{$method}]");	}
		}

		/**
		 * Register the application services.
		 * 注册应用程序服务，register方法不能少
		 * @return void
		 */
		public function register()
		{
			//
		}

	}


如果想刚才建立的注册服务者能被使用，则必须在/config/app.php文件中的providers key中配置： 如'providers' => [ 'App\Providers\JellyServiceProvider', ]


缓存服务提供者的定义是defer属性为真，且再重写方法
	<?php namespace App\Providers;
		use Illuminate\Support\ServiceProvider;
		class JellyccServiceProvider extends ServiceProvider {
			 protected $defer = true; //为真true 则标记是延缓提供者加载
			/**
			 * Bootstrap the application services.
			 * @return void
			 */
			public function boot()
			{
			   //这个方法可有可无，根据业务来写，父类的__call方法有实现调用不存在的boot方法时返回空： public function __call($method, $parameters){if ($method == 'boot') return;	throw new BadMethodCallException("Call to undefined method [{$method}]");	}
			}

			/**
			 * Register the application services.
			 * 注册应用程序服务，register方法不能少
			 * @return void
			 */
			public function register()
			{
				//
			}
			/**
			 * 取得提供者所提供的服务
			 * @return array
			*/
   		        public function provides(){
				return ['Xxx\Contracts\Connection类名'];
			 }

		}
