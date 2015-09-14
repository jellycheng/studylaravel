## learn Laravel PHP Framework

### Laravel项目目录结构说明：
	|- vendor 目录包含你的 Composer 依赖模块及laravel框架。
	|- bootstrap 目录包含几个框架启动跟自动加载配置的文件。
		|- app.php
		|- autoload.php
	|- config 应用程序的配置文件。
	|- database 数据库迁移与数据填充文件。
	|- public 项目web入口和静态资源文件 (图片、js、css，字体等等)。
	    |-index.php 入口
	|- resources 目录包含视图、原始的资源文件 (LESS、SASS、CoffeeScript) 和「语言」文件。
		|- views 视图模板目录
		|- lang 语言包目录
		|- assets
			|- less
	|- storage 目录包含编译后的 Blade 模板、基于文件的 session、文件缓存和其他框架产生的文件，该目录一定要可读可写。
	|- tests 自动化测试目录
	|- app 目录包含应用程序的核心代码
		|- Console 是Artisan 命令
		|- Commands 用来放置应用程序的命
		|- Events 用来放置事件类
		|- Exceptions
		|- Handlers 包含命令和事件的处理类。处理进程接收命令或事件，并针对该命令或事件执行逻辑
		|- Http
			|- Controllers 控制器目录
			    |-HomeController.php
			    |-XxxController.php
			|- Middleware 中间件目录
			|- Requests
		|- Exceptions 应用程序的异常处理进程，
		|- Services 目录包含各种「辅助」服务，囊括应用程序需要的功能。
		|- Providers 
    |-artisan 是cli脚本入口



