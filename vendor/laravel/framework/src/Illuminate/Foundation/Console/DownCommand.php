<?php namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;

class DownCommand extends Command {

	/**
	 * The console command name.
	 * 执行命令: php artisan down
	 * @var string
	 */
	protected $name = 'down';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Put the application into maintenance mode";

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		touch($this->laravel->storagePath().'/framework/down');//新建一个文件,表进入维护模式

		$this->comment('Application is now in maintenance mode.');
	}

}
