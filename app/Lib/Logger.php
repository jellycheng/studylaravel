<?php
namespace App\Lib;
use Monolog\Logger as BaseLogger;
use Monolog\Handler\StreamHandler;


/**
 * 扩展monogo日志类，使用例子如下：
 * $fh = new \Monolog\Handler\RotatingFileHandler(
                                                    '/data1/logs/app/tt/xxx.log',
                                                    0,
                                                    'debug',
                                                    false
                                                    );
    $fh->setFilenameFormat('{filename}.{date}', 'Y-m-d');//文件格式
    $formatter = new \Monolog\Formatter\LineFormatter("%datetime% %channel% %level_name% %request_id% %message% %context%\n");//日志内容格式
    $fh->setFormatter($formatter);//文件对象设置内容格式对象
    //return new \Monolog\Logger(Config::get('log.name', 'app'), array($fh));
    return new \App\Lib\Logger(Config('log.channel', 'jigou-service'), array($fh));
 */
class Logger extends BaseLogger
{
    protected $microsecondTimestamps = true;

    /**
     * Adds a log record.
     *
     * @param  int     $level   The logging level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addRecord($level, $message, array $context = array())
    {
        if (!$this->handlers) {
            $this->pushHandler(new StreamHandler('php://stderr', static::DEBUG));
        }

        $levelName = static::getLevelName($level);

        // check if any handler will handle this message so we can return early and save cycles
        $handlerKey = null;
        reset($this->handlers);
        while ($handler = current($this->handlers)) {
            if ($handler->isHandling(array('level' => $level))) {
                $handlerKey = key($this->handlers);
                break;
            }

            next($this->handlers);
        }

        if (null === $handlerKey) {
            return false;
        }

        if (!static::$timezone) {
            static::$timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
        }

        if ($this->microsecondTimestamps) {
            $ts = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), static::$timezone);
        } else {
            $ts = new \DateTime(null, static::$timezone);
        }
        $ts->setTimezone(static::$timezone);

        $record = array(
            'message' => '"' . (string) $message . '"',
            'context' => $context,
            'level' => $level,
            'level_name' => $levelName,
            'channel' => $this->name,
            'datetime' => $ts,
            'request_id' => microtime(true), //uuid
            'extra' => []
        );

        foreach ($this->processors as $processor) {
            $record = call_user_func($processor, $record);
        }

        while ($handler = current($this->handlers)) {
            if (true === $handler->handle($record)) {
                break;
            }

            next($this->handlers);
        }

        return true;
    }

}
