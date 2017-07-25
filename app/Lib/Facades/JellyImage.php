<?php
namespace App\Lib\Facades;

use Illuminate\Support\Facades\Facade;
//使用方式1：echo \App\Lib\Facades\jellyImage::hello();
//    方式2： 设置别名：config/app.php中'aliases' => ['其它别名key'=>'其它类名','JellyImage'=>'App\Lib\Facades\jellyImage',]
//            use JellyImage; JellyImage::hello(); 或者 \JellyImage::hello();
class JellyImage extends Facade
{
    protected static function getFacadeAccessor() {
        return 'jellyImage';  
        /**配合 App\Providers\JellyServiceProvider.php文件中单例代码使用，如下
         * $this->app->singleton('jellyImage', function ($app) {
            return new \App\Lib\JellyImage();
            });
         */ 
    }
}