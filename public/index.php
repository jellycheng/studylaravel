<?php
/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/
//composer加载器
require __DIR__.'/../bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/
//返回laravel app对象
$app = require_once __DIR__.'/../bootstrap/app.php';
//echo app('path.database');exit;
/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
| $对象 = $app->make('抽象物');
*/

$kernel = $app->make('Illuminate\Contracts\Http\Kernel');//返回App\Http\Kernel类对象
//var_export($kernel);
$response = $kernel->handle(
	$request = Illuminate\Http\Request::capture()  //请求对象
);

$response->send();//响应内容: 设置响应头和输出响应内容

$kernel->terminate($request, $response);//执行terminate类型中间介的terminate()方法
