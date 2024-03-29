<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/
//实例化app对象
$app = new Illuminate\Foundation\Application(
	realpath(__DIR__.'/../')   //项目代码根目录 如d:/jellyphp/studylaravel
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
| 格式:$app->singleton(抽象物,实现物); 等价 $app->bind(抽象物,实现物, true);
| $app->singleton('Illuminate\Contracts\Http\Kernel','App\Http\Kernel');等价
| $app->bind('Illuminate\Contracts\Http\Kernel','App\Http\Kernel', true);
| 其实是设置app对象的bindings属性值
| 一般接口名做抽象物，具体接口实现类做实现物（即具体物）
*/

$app->singleton(
	'Illuminate\Contracts\Http\Kernel',
	'App\Http\Kernel'
);

$app->singleton(
	'Illuminate\Contracts\Console\Kernel',
	'App\Console\Kernel'
);

$app->singleton(
	'Illuminate\Contracts\Debug\ExceptionHandler',
	'App\Exceptions\Handler'
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
