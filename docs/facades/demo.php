<?php
//facade思路 demo
header("Content-type: text/html; charset=utf-8");


//注册facade配置，通过别名自动加载器加载
class RegisterFacades {

	public function bootstrap(Application $app, $config)
	{
		\Illuminate\Support\Facades\Facade::clearResolvedInstances();//清空以前设置的facade
		
		\Illuminate\Support\Facades\Facade::setFacadeApplication($app);//注入app对象
		#注册别名并设置自动加载器,app.aliases在laravel是config/app.php中的aliases配置key值
		\Illuminate\Foundation\AliasLoader::getInstance($config['app.aliases'])->register();
	}

}
//把对象存到app对象的属性中，实现单例
class testjelly{

	public function hello() {

		echo "hello..." . PHP_EOL;
	}
}
class Application implements ArrayAccess{
	public function instance($abstrace, $instance) {

		//todo
	}

	public function offsetExists($key)	{	}

	public function offsetGet($key)	{//实例化类对象
		
		return $this->make($key);
	}

	public function make($key) {//实例化类 单例todo

		return new $key();
	}

	/**
	 * Set a configuration option.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function offsetSet($key, $value)	{ }

	/**
	 * Unset a configuration option.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key){	}

}

$app = new Application();//换成注入app对象即可，这里只是做一个demo

function abc($class) {
	//加载别名对应的类 todo
	//Illuminate\Support\Facades
	require_once 'facade/TestJelly.php';
}
spl_autoload_register("abc");

require_once 'facade/Facade.php';
require_once 'AliasLoader.php';
require_once 'facade/TestJelly.php';

$appConfig = array(
				'app.aliases'=>array(
							//类别名=>facades类名
							'App'       => 'Illuminate\Support\Facades\App',//别名对应的类
							'Artisan'   => 'Illuminate\Support\Facades\Artisan',
							'Auth'      => 'Illuminate\Support\Facades\Auth',
							'Blade'     => 'Illuminate\Support\Facades\Blade',
							'Bus'       => 'Illuminate\Support\Facades\Bus',
							'Cache'     => 'Illuminate\Support\Facades\Cache',
							'Config'    => 'Illuminate\Support\Facades\Config',
							'Cookie'    => 'Illuminate\Support\Facades\Cookie',
							'Crypt'     => 'Illuminate\Support\Facades\Crypt',
							'DB'        => 'Illuminate\Support\Facades\DB',
							'Eloquent'  => 'Illuminate\Database\Eloquent\Model',
							'Event'     => 'Illuminate\Support\Facades\Event',
							'File'      => 'Illuminate\Support\Facades\File',
							'Hash'      => 'Illuminate\Support\Facades\Hash',
							'Input'     => 'Illuminate\Support\Facades\Input',
							'Inspiring' => 'Illuminate\Foundation\Inspiring',
							'Lang'      => 'Illuminate\Support\Facades\Lang',
							'Log'       => 'Illuminate\Support\Facades\Log',
							'Mail'      => 'Illuminate\Support\Facades\Mail',
							'Password'  => 'Illuminate\Support\Facades\Password',
							'Queue'     => 'Illuminate\Support\Facades\Queue',
							'Redirect'  => 'Illuminate\Support\Facades\Redirect',
							'Redis'     => 'Illuminate\Support\Facades\Redis',
							'Request'   => 'Illuminate\Support\Facades\Request',
							'Response'  => 'Illuminate\Support\Facades\Response',
							'Route'     => 'Illuminate\Support\Facades\Route',
							'Schema'    => 'Illuminate\Support\Facades\Schema',
							'Session'   => 'Illuminate\Support\Facades\Session',
							'Storage'   => 'Illuminate\Support\Facades\Storage',
							'URL'       => 'Illuminate\Support\Facades\URL',
							'Validator' => 'Illuminate\Support\Facades\Validator',
							'View'      => 'Illuminate\Support\Facades\View',
							'TestJelly1' => 'Illuminate\Support\Facades\TestJelly',
					),
				);

$registerFacades = new  RegisterFacades();
$registerFacades->bootstrap($app, $appConfig);

TestJelly1::hello(); #调用facade的TestJelly1类的__callStatic(hello, array())
