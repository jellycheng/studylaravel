## learn Laravel PHP Framework
### Laravel项目目录结构说明：
	|- vendor 目录：包含Composer依赖模块及laravel框架。
	|- bootstrap 目录:主要包含包含框架启动跟自动加载配置的文件。
		|- app.php	 #app初始化
		|- autoload.php  #composer autoload
	|- config 应用程序的配置文件，每个配置文件均返回数组格式。
	|- database 数据库迁移与数据填充文件。
	|- public 项目web入口和静态资源文件 (图片、js、css，字体等等)。
	    |-index.php 入口
	|- resources 目录包含视图、原始的资源文件 (LESS、SASS、CoffeeScript) 和「语言」文件。
		|- views 视图模板目录
		|- lang 语言包目录
		|- assets
			|- less
	|- storage 目录包含编译后的 Blade 模板、基于文件的 session、文件缓存和其他框架产生的文件，该目录一定要可读可写。
	|- tests 单元测试代码目录
	|- app 目录包含应用程序的核心代码
		|- Console 是Artisan 命令
		|- Commands 用来放置应用程序的命令
		|- Events 用来放置事件类
		|- Handlers 包含命令和事件的处理类。处理进程接收命令或事件，并针对该命令或事件执行逻辑
		|- Http
			|- Controllers 控制器目录
			    |-HomeController.php
			    |-XxxController.php
			|- Middleware 中间件目录
			|- Requests
		        |- routes.php 路由配置文件
			|- Kernel.php  App\Http\Kernel类定义
		|- Exceptions 应用程序的异常处理进程，
		|- Services 目录包含各种「辅助」服务，囊括应用程序需要的功能。
		|- Providers 服务提供者目录
		|- Model  开发自加的，存放db模型类，一个表一个文件，一个db一个文件夹，数据层
		|- Modules 业务聚合类
		|- Lib 开发自加的，存放代码类库，跟业务逻辑无关的类库，解耦
    |-artisan 是cli脚本入口


###相关资料
```
官网：  https://laravel.com/
laravel中文手册：
	http://www.golaravel.com/
	https://cs.laravel-china.org/
PHP 标准规范中文版: 
	https://psr.phphub.org/
	http://www.php-fig.org/

env环境设置类： https://github.com/vlucas/phpdotenv
http://php-di.org/doc/getting-started.html
http://element.eleme.io/
http://www.jemui.com/
```


###获取laravel最新版本代码
```
1.获取laravel最新版本代码-方式1： http://cabinet.laravel.com/latest.zip  解压，然后执行composer install安装依赖包即可
2.获取laravel最新版本代码-方式2：
	安装器： composer global require "laravel/installer"
	新建一个项目名： laravel new 项目名  如 laravel new blog
3. 获取laravel最新代码-方式3： composer create-project --prefer-dist laravel/laravel 项目名 如 composer create-project --prefer-dist laravel/laravel blog


```

