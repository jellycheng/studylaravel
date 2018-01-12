<?php namespace Illuminate\Foundation\Console;

use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Console\Kernel as KernelContract;

class Kernel implements KernelContract {

	/**
	 * The application implementation.
	 *
	 * @var \Illuminate\Contracts\Foundation\Application
	 */
	protected $app;

	/**
	 * The event dispatcher implementation.
	 *
	 * @var \Illuminate\Contracts\Events\Dispatcher
	 */
	protected $events;

	/**
	 * The Artisan application instance.
	 *
	 * @var \Illuminate\Console\Application
	 */
	protected $artisan;

	/**
	 * The bootstrap classes for the application.
	 *
	 * @var array
	 */
	protected $bootstrappers = [
		'Illuminate\Foundation\Bootstrap\DetectEnvironment', //分析.env文件，并设置当前环境
		'Illuminate\Foundation\Bootstrap\LoadConfiguration',//加载config配置文件，设置时区,设置编码,可以使用$app['config']['app.aliases']获取配置值
		'Illuminate\Foundation\Bootstrap\ConfigureLogging',//设置日志,可通过app['log']获取日志对象,写日志app['log']->info("信息内容");等价Log::info('信息内容');
		'Illuminate\Foundation\Bootstrap\HandleExceptions',//异常handle设置,set_error_handler(),set_exception_handler(),register_shutdown_function()
		'Illuminate\Foundation\Bootstrap\RegisterFacades',//Facades类注入app对象，别名自动加载器,即把config/app.php中aliases配置的值定义好别名
		'Illuminate\Foundation\Bootstrap\SetRequestForConsole',//容器中设置请求对象
		'Illuminate\Foundation\Bootstrap\RegisterProviders',//调用app对象->registerConfiguredProviders()，并执行服务提供者类的register()方法,服务提供者类来自config/app.php的providers配置key
		'Illuminate\Foundation\Bootstrap\BootProviders',//调用app对象->boot()方法（即执行所有服务提供者的boot()方法,上一行代码中设置的）
	];

	/**
	 * Create a new console kernel instance.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application接口实现类  $app
	 * @param  \Illuminate\Contracts\Events\Dispatcher接口实现类  $events
	 * @return void
	 */
	public function __construct(Application $app, Dispatcher $events)
	{
		$this->app = $app;
		$this->events = $events;
		$this->defineConsoleSchedule();
	}

	/**
	 * Define the application's command schedule.
	 *
	 * @return void
	 */
	protected function defineConsoleSchedule()
	{
		$this->app->instance(
			'Illuminate\Console\Scheduling\Schedule', $schedule = new Schedule
		);

		$this->schedule($schedule);
	}

	/**
	 * Run the console application.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @return int
	 */
	public function handle($input, $output = null)
	{
		try
		{
			$this->bootstrap();//启动初始化

			return $this->getArtisan()->run($input, $output);//z执行console app的run方法
		}
		catch (Exception $e)
		{
			$this->reportException($e);

			$this->renderException($output, $e);

			return 1;
		}
	}

	/**
	 * Terminate the application.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  int  $status
	 * @return void
	 */
	public function terminate($input, $status)
	{
		$this->app->terminate();
	}

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		//
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
		$this->bootstrap();

		$this->app->loadDeferredProviders();

		return $this->getArtisan()->call($command, $parameters);
	}

	/**
	 * Queue the given console command.
	 *
	 * @param  string  $command
	 * @param  array   $parameters
	 * @return void
	 */
	public function queue($command, array $parameters = array())
	{
		$this->app['Illuminate\Contracts\Queue\Queue']->push(
			'Illuminate\Foundation\Console\QueuedJob', func_get_args()
		);
	}

	/**
	 * Get all of the commands registered with the console.
	 *
	 * @return array
	 */
	public function all()
	{
		$this->bootstrap();

		return $this->getArtisan()->all();
	}

	/**
	 * Get the output for the last run command.
	 *
	 * @return string
	 */
	public function output()
	{
		$this->bootstrap();

		return $this->getArtisan()->output();
	}

	/**
	 * Bootstrap the application for HTTP requests.
	 *
	 * @return void
	 */
	public function bootstrap()
	{
		if ( ! $this->app->hasBeenBootstrapped())
		{//app未启动完毕
			$this->app->bootstrapWith($this->bootstrappers());//调用启动执行的类,且这些类均有bootstrap($app对象)方法
		}
		$this->app->loadDeferredProviders();//执行延迟服务提供者
	}

	/**
	 * Get the Artisan application instance.
	 * 获取Artisan类对象并设置本类commands属性值
	 * @return \Illuminate\Console\Application
	 */
	protected function getArtisan()
	{
		if (is_null($this->artisan))
		{   //实例化console app对象，同时实例化命令类并设置console app容器的commands属性=['命令类名'=>对象, '命令类别名'=>对象]
			return $this->artisan = (new Artisan($this->app, $this->events))
								->resolveCommands($this->commands);
		}

		return $this->artisan;
	}

	/**
	 * Get the bootstrap classes for the application.
	 *
	 * @return array
	 */
	protected function bootstrappers()
	{
		return $this->bootstrappers;
	}

	/**
	 * Report the exception to the exception handler.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	protected function reportException(Exception $e)
	{
		$this->app['Illuminate\Contracts\Debug\ExceptionHandler']->report($e);
	}

	/**
	 * Report the exception to the exception handler.
	 *
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 * @param  \Exception  $e
	 * @return void
	 */
	protected function renderException($output, Exception $e)
	{
		$this->app['Illuminate\Contracts\Debug\ExceptionHandler']->renderForConsole($output, $e);
	}

}
