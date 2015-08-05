<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>/resources/views/jelly/demo1.blade.php</title>
 </head>
 <body>
  <h1>/resources/views/jelly/demo1.blade.php</h1>
<pre>
  控制器中使用： return View('jelly.emo'); 或者 return View('jelly.emo', 数组是要传给模板的变量);

  路由中配置： /app/Http/routes.php 文件追加 Route::get('jelly/tpldemo1', 'JellyController@tpldemo1');
  访问url： http://localhost/learnlaravel/public/jelly/tpldemo1
  
  控制器类写法：
	&lt;?php namespace App\Http\Controllers;
	use App\Http\Requests;
	use App\Http\Controllers\Controller;
	use Illuminate\Http\Request;
	class JellyController extends Controller {
		public function tpldemo1() {
			return View('jelly.demo1');
		}
	}

</pre>
 </body>
</html>