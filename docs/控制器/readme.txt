

代码位置D:\jellyphp\learnlaravel\

注意nginx中要配置重写：http://localhost/learnlaravel/public/jelly
	#location / {
	#    try_files $uri $uri/ /index.php?$query_string;
	#}
	#或者
	root   D:\jellyphp;
        location / {
            #root   html;
            index  index.html index.htm index.php;
	    try_files $uri $uri/ /learnlaravel/public/index.php?$query_string;
        }

控制器：
	使用命令行创建控制器：  php artisan make:controller 控制器名Controller 如 php artisan make:controller JellyController
控制器类名首字母大写： 类名 JellyController=》文件名JellyController.php =>目录位置：/app/Http/Controllers
模板文件存放位置：/resources/views/
JellyController.php文件内容如下：
	<?php namespace App\Http\Controllers;
	use App\Http\Requests;
	use App\Http\Controllers\Controller;
	use Illuminate\Http\Request;
	class JellyController extends Controller {
		/**
		 * 在/app/Http/routes.php文件中配置路由 Route::get('jelly', 'JellyController@index');
		 */
		public function index()
		{
			echo __FILE__;
		}
		public function tplDemo() {
			return View('jellyDemo');//加载模板/resources/views/jellyDemo.blade.php
		}
	}

控制器生成好，之后，还不能通过url直接访问，需要配置路由才能访问。
路由配置文件： /app/Http/routes.php{
	Route::get('jellyabc', function() {
		$f = get_included_files();
		return "sdfad==jellyabc，访问地址是http://localhost/learnlaravel/public/jellyabc" . var_export($f,true);
	});

	Route::get('jelly', 'JellyController@index'); #对应地址 http://localhost/learnlaravel/public/jelly
}



