<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
/**
 * 改写日志服务,改写Illuminate\Foundation\Bootstrap\ConfigureLogging类中设置的规则
 * 在config/app.php配置文件的providers key中配置'App\Providers\LogServiceProvider',
 */
class  LogServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $logPath = "/data1/logs/app/xfcrm-web";
        if (!is_dir($logPath)) mkdir($logPath, 0777, true);
        $log = $this->app['log'];
        //$log->getMonolog()->getName(); 返回monolog的channel值，laravel传入的是环境代号  //echo $log->getMonolog()->getName();exit;
        //var_export($log->getMonolog()->getHandlers());exit;//Monolog\Handler\RotatingFileHandler
        /**
        //增加一种handler
        $handler = new \Monolog\Handler\RotatingFileHandler($logPath . '/xfcrm-web.log');
        $handler->setFilenameFormat('{filename}.{date}', 'Y-m-d');
        $log->getMonolog()->pushHandler($handler);//新增日志处理句柄方式
        $requestId = uniqid(mt_rand(100000, 999999).'-');
        $handler->setFormatter(new \Monolog\Formatter\LineFormatter(
                                                                    //"%datetime% %channel% %level_name% %request_id% %message% %context%\n",
                                                                    "%datetime% xfcrm-web %level_name% {$requestId} %message% %context%\n",
                                                                    null,
                                                                    true
                                                                )
                            );
        */
        $this->changeHnalers($log);
        //exit;
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * 修改\Monolog\Handler\RotatingFileHandler日志格式
     * @param $log
     */
    protected function changeHnalers($log) {
        $monolog = $log->getMonolog();//monolog对象
        if (empty($monolog)) {
            return;
        }
        $handlers = $monolog->getHandlers();
        foreach ($handlers as $k=>$obj) {
            //var_dump($obj);
            if ($obj instanceof \Monolog\Handler\RotatingFileHandler ) {
                $obj->setFilenameFormat('{filename}.{date}', 'Y-m-d');
                //统一日志格式： 日期时间 应用名 日志等级 REQUESTID "MESSAGE描述" 日志内容
                $obj->setFormatter(new \Monolog\Formatter\LineFormatter(
                        "%datetime% xfcrm-web %level_name% 123 %message% %context%\n",
                        null,
                        true
                    )
                );
            }
        }
    }

    /**
     * 移除已注册的log handler
     * @param \Illuminate\Log\Writer $log
     */
    protected function removeRegisteredHandlers($log)
    {
        $monolog = $log->getMonolog();//monolog对象
        if (empty($monolog)) {
            return;
        }
        $handlers = $monolog->getHandlers();
        for ($total = count($handlers); $total > 0; $total--) {
            $monolog->popHandler();
        }
    }
    //移除已注册的log handler 跟removeRegisteredHandlers方法作用一样
    protected function clearHandlers()
    {
        $iHandlersCount = count(app('log')->getMonolog()->getHandlers());
        while ($iHandlersCount) {
            app('log')->getMonolog()->popHandler();
            $iHandlersCount--;
        }
    }
    
}