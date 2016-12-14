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
	 *
	 * @var string
	 */
	protected $manifestPath;

	/**
	 * Create a new service repository instance.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @param  \Illuminate\Filesystem\Filesystem  $files
	 * @param  string  $manifestPath
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
	 * @param  array  $providers
	 * @return void
	 */
	public function load(array $providers)
	{
		$manifest = $this->loadManifest();

		// First we will load the service manifest, which contains information on all
		// service providers registered with the application and which services it
		// provides. This is used to know which services are "deferred" loaders.
		if ($this->shouldRecompile($manifest, $providers))
		{//需要重新编译
			$manifest = $this->compileManifest($providers);
		}

		// Next, we will register events to load the providers for each of the events
		// that it has requested. This allows the service provider to defer itself
		// while still getting automatically loaded when a certain event occurs.
		foreach ($manifest['when'] as $provider => $events)
		{
			$this->registerLoadEvents($provider, $events);
		}

		// We will go ahead and register all of the eagerly loaded providers with the
		// application so their services can be registered with the application as
		// a provided service. Then we will set the deferred service list on it.
		foreach ($manifest['eager'] as $provider)
		{
			$this->app->register($this->createProvider($provider));
		}

		$this->app->setDeferredServices($manifest['deferred']);
	}

	/**
	 * Register the load events for the given provider.
	 *
	 * @param  string  $provider
	 * @param  array  $events
	 * @return void
	 */
	protected function registerLoadEvents($provider, array $events)
	{
		if (count($events) < 1) return;

		$app = $this->app;

		$app->make('events')->listen($events, function() use ($app, $provider)
		{
			$app->register($provider);
		});
	}

	/**
	 * Compile the application manifest file.
	 * 重新编译服务提供者类
	 * @param  array  $providers
	 * @return array
	 */
	protected function compileManifest($providers)
	{
		$manifest = $this->freshManifest($providers);

		foreach ($providers as $provider)
		{
			$instance = $this->createProvider($provider);
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
	 *
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
	 * 加载配置文件
	 * @return array
	 */
	public function loadManifest()
	{
		// The service manifest is a file containing a JSON representation of every
		// service provided by the application and whether its provider is using
		// deferred loading or should be eagerly loaded on each request to us.
		if ($this->files->exists($this->manifestPath))
		{
			$manifest = json_decode($this->files->get($this->manifestPath), true);

			return array_merge(['when' => []], $manifest);
		}
	}

	/**
	 * Write the service manifest file to disk.
	 * 写入配置文件
	 * @param  array  $manifest
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
	 *
	 * @param  array  $providers
	 * @return array
	 */
	protected function freshManifest(array $providers)
	{
		return ['providers' => $providers, 'eager' => [], 'deferred' => []];
	}

}
