<?php namespace Illuminate\Foundation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;

class ProviderRepository {

	/**
	 * The application implementation.
	 *
	 * @var \Illuminate\Contracts\Foundation\Application
	 */
	protected $app;

	/**
	 * The filesystem instance.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * The path to the manifest file.
	 * 服务提供者的json文件
	 * @var string
	 */
	protected $manifestPath;

	/**
	 * Create a new service repository instance.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @param  \Illuminate\Filesystem\Filesystem  $files
	 * @param  string  $manifestPath  服务提供者的json文件
	 * @return void
	 */
	public function __construct(ApplicationContract $app, Filesystem $files, $manifestPath)
	{
		$this->app = $app;
		$this->files = $files;
		$this->manifestPath = $manifestPath;
	}

	/**
	 * Register the application service providers.
	 *
	 * @param  array  $providers  值来源于config/app.php文件的providers配置key值
	 * @return void
	 */
	public function load(array $providers)
	{	//获取的是vendor/services.json文件内容
		$manifest = $this->loadManifest();//配置文件存在则返回数组内容，否则返回null
		if ($this->shouldRecompile($manifest, $providers))
		{//cache不存在或提供者配置不一致则需要重新编译
			$manifest = $this->compileManifest($providers);
		}
		foreach ($manifest['when'] as $provider => $events)
		{
			$this->registerLoadEvents($provider, $events);
		}
		foreach ($manifest['eager'] as $provider)
		{//循环非延迟加载服务提供者类,注册服务提供者并执行服务提供者->register()方法
			$this->app->register($this->createProvider($provider));
		}
		//设置app类的setDeferredServices属性值
		$this->app->setDeferredServices($manifest['deferred']);
	}

	/**
	 * Register the load events for the given provider.
	 *
	 * @param  string  $provider 提供者类
	 * @param  array  $events 事件数组
	 * @return void
	 */
	protected function registerLoadEvents($provider, array $events)
	{
		if (count($events) < 1) return;
		$app = $this->app;
		$app->make('events')->listen($events, function() use ($app, $provider)
		{	//监听事件，事件触发则注册服务提供者
			$app->register($provider);
		});
	}

	/**
	 * Compile the application manifest file.
	 * 重新编译服务提供者类,写入cache文件
	 * @param  array  $providers
	 * @return array
	 */
	protected function compileManifest($providers)
	{
		$manifest = $this->freshManifest($providers);//获取最新结构
		foreach ($providers as $provider)
		{//$provider类名
			$instance = $this->createProvider($provider);//实例化服务提供者类
			if ($instance->isDeferred())
			{
				foreach ($instance->provides() as $service)
				{
					$manifest['deferred'][$service] = $provider;
				}

				$manifest['when'][$provider] = $instance->when();
			} else
			{
				$manifest['eager'][] = $provider;
			}
		}

		return $this->writeManifest($manifest);
	}

	/**
	 * Create a new provider instance.
	 * 实例化服务提供者类
	 * @param  string  $provider
	 * @return \Illuminate\Support\ServiceProvider
	 */
	public function createProvider($provider)
	{
		return new $provider($this->app);
	}

	/**
	 * Determine if the manifest should be compiled.
	 * $manifest为空，或者 $manifest['providers']!=$providers 即已经cache的提供者不一致
	 * @param  array  $manifest
	 * @param  array  $providers
	 * @return bool
	 */
	public function shouldRecompile($manifest, $providers)
	{
		return is_null($manifest) || $manifest['providers'] != $providers;
	}

	/**
	 * Load the service provider manifest JSON file.
	 * 加载配置文件=项目目录/vendor/services.json
	 * @return array
	 */
	public function loadManifest()
	{
		if ($this->files->exists($this->manifestPath))
		{//文件存在，获取文件内容
			$manifest = json_decode($this->files->get($this->manifestPath), true);

			return array_merge(['when' => []], $manifest);
		}
	}

	/**
	 * Write the service manifest file to disk.
	 * 写入配置文件(vendor/services.json)
	 * @param  array  $manifest 要写入的内容
	 * @return array
	 */
	public function writeManifest($manifest)
	{
		$this->files->put(
			$this->manifestPath, json_encode($manifest, JSON_PRETTY_PRINT)
		);

		return $manifest;
	}

	/**
	 * Create a fresh service manifest data structure.
	 * [
	 * 'providers'=>'',
	 * 	'eager'=>非延迟加载服务提供者类,
	 * 'deferred'=>
	 * ]
	 * @param  array  $providers 服务提供者
	 * @return array
	 */
	protected function freshManifest(array $providers)
	{
		return ['providers' => $providers, 'eager' => [], 'deferred' => []];
	}

}
