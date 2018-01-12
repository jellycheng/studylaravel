<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;

class Inspire extends Command {

	/**
	 * The console command name.
	 * 命令名
	 * @var string
	 */
	protected $name = 'inspire';

	/**
	 * The console command description.
	 * 命令描述
	 * @var string
	 */
	protected $description = 'Display an inspiring quote';

	/**
	 * Execute the console command.
	 * 这里也业务逻辑
	 * @return mixed
	 */
	public function handle()
	{
		$this->comment(PHP_EOL.Inspiring::quote().PHP_EOL);
	}

}
