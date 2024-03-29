<?php namespace Illuminate\Console;

use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Illuminate\Contracts\Console\Application as ApplicationContract;
use Illuminate\Contracts\Foundation\Application as LaravelApplication;

class Application extends SymfonyApplication implements ApplicationContract {

	/**
	 * The Laravel application instance.
	 *
	 * @var \Illuminate\Contracts\Foundation\Application
	 */
	protected $laravel;

	/**
	 * The event dispatcher implementation.
	 *
	 * @var \Illuminate\Contracts\Events\Dispatcher
	 */
	protected $events;

	/**
	 * The output from the previous command.
	 *
	 * @var \Symfony\Component\Console\Output\OutputInterface
	 */
	protected $lastOutput;

	/**
	 * Create a new Artisan console application.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $laravel 是laravel app容器对象
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events 事件对象
	 * @return void
	 */
	public function __construct(LaravelApplication $laravel, Dispatcher $events)
	{
		parent::__construct('Laravel Framework', $laravel->version());

		$this->event = $events;
		$this->laravel = $laravel;
		$this->setAutoExit(false);
		$this->setCatchExceptions(false);

		$events->fire('artisan.start', [$this]);
	}

	/**
	 * Run an Artisan console command by name.
	 *
	 * @param  string  $command
	 * @param  array  $parameters
	 * @return int
	 */
	public function call($command, array $parameters = array())
	{
		$parameters['command'] = $command;

		$this->lastOutput = new BufferedOutput;

		return $this->find($command)->run(new ArrayInput($parameters), $this->lastOutput);
	}

	/**
	 * Get the output for the last run command.
	 *
	 * @return string
	 */
	public function output()
	{
		return $this->lastOutput ? $this->lastOutput->fetch() : '';
	}

	/**
	 * Add a command to the console.
	 *
	 * @param  \Symfony\Component\Console\Command\Command  $command 命令类名
	 * @return \Symfony\Component\Console\Command\Command
	 */
	public function add(SymfonyCommand $command)
	{
		if ($command instanceof Command)
		{//是Illuminate\Console\Command类对象则注入app对象
			$command->setLaravel($this->laravel);
		}
        //设置本类commands属性值
		return $this->addToParent($command);
	}

	/**
	 * Add the command to the parent instance.
	 * 设置本类commands属性值
	 * @param  \Symfony\Component\Console\Command\Command  $command
	 * @return \Symfony\Component\Console\Command\Command
	 */
	protected function addToParent(SymfonyCommand $command)
	{
		return parent::add($command);
	}

	/**
	 * Add a command, resolving through the application.
	 *
	 * @param  string  $command 命令类名
	 * @return \Symfony\Component\Console\Command\Command
	 */
	public function resolve($command)
	{   //实例化命令类并设置console app容器的commands属性=['命令类名'=>对象, '命令类别名'=>对象]
		return $this->add($this->laravel->make($command));
	}

	/**
	 * Resolve an array of commands through the application.
	 * 批量设置本类commands属性值
	 * @param  array|mixed  $commands
	 * @return $this
	 */
	public function resolveCommands($commands)
	{
		$commands = is_array($commands) ? $commands : func_get_args();
		foreach ($commands as $command)
		{//循环每个命令
			$this->resolve($command);//实例化命令类并设置console app容器的commands属性=['命令类名'=>对象, '命令类别名'=>对象]
		}
		return $this;
	}

	/**
	 * Get the default input definitions for the applications.
	 *
	 * This is used to add the --env option to every available command.
	 *
	 * @return \Symfony\Component\Console\Input\InputDefinition
	 */
	protected function getDefaultInputDefinition()
	{
		$definition = parent::getDefaultInputDefinition();

		$definition->addOption($this->getEnvironmentOption());

		return $definition;
	}

	/**
	 * Get the global environment option for the definition.
	 *
	 * @return \Symfony\Component\Console\Input\InputOption
	 */
	protected function getEnvironmentOption()
	{
		$message = 'The environment the command should run under.';

		return new InputOption('--env', null, InputOption::VALUE_OPTIONAL, $message);
	}

	/**
	 * Get the Laravel application instance.
	 *
	 * @return \Illuminate\Contracts\Foundation\Application
	 */
	public function getLaravel()
	{
		return $this->laravel;
	}

}
