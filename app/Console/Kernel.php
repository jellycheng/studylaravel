<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 * 配置可执行的命令行代码
	 * @var array
	 */
	protected $commands = [
		'App\Console\Commands\Inspire',   #php artisan inspire
        'App\Console\Commands\CreateModelCommand', #php artisan create:appmodel
	];

	/**
	 * Define the application's command schedule.
	 * 在crontab中配置 * * * * * php /path/to/artisan schedule:run 1>> /dev/null 2>&1 会执行这个代码
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('inspire')->hourly();//每小时执行一次
        //可以设置指定日期，时间，星期及环境执行的命令

	}

}
