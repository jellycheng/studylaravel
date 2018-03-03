
<?php namespace App\Providers;

use App\Library\Logger;
use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Formatter\LineFormatter;

/**
 * Class LoggerServiceProvider
 *
 * @package App\Providers
 */
class LoggerServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->forgetInstance('log');//删除以前的log对象
        $logPath = env('PATH_STORAGE') ? env('PATH_STORAGE') . '/api-service/api-service.log'
            : storage_path() . '/logs/api-service.log';

        //保存7天日志文件
        $handler = new RotatingFileHandler($logPath, 10, MonologLogger::DEBUG, true, 0666);
        $formatter = "%datetime% %channel% %level_name% %request_id% %message% %context%\n";
        $handler->setFormatter(new LineFormatter($formatter, null, true, true));

        //持久化日志文件
        $handlerCritical = new RotatingFileHandler($logPath . 'x', 180, MonologLogger::CRITICAL, true, 0666);
        $formatter = "%datetime% %channel% %level_name% %request_id% %message% %context%\n";
        $handlerCritical->setFormatter(new LineFormatter($formatter, null, true, true));

        $log = new Writer(new Logger($this->app->environment()), $this->app['events']);
        $log->getMonolog()->pushHandler($handler);
        $log->getMonolog()->pushHandler($handlerCritical);
        $this->app->instance('log', $log);//重新注入log对象,这样其它地方就可以直接\Log::info("info级别日志")调用了

    }

}