<?php namespace Illuminate\Foundation\Bootstrap;

use Illuminate\Config\Repository;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Config\Repository as RepositoryContract;

class LoadConfiguration {

	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		$items = [];

		if (file_exists($cached = $app->getCachedConfigPath()))
		{//存在storage/framwwork/config.php文件则加载
			$items = require $cached;
			$loadedFromCache = true;//标记加载过cache文件
		}
        //把config对象注入app对象容器中
		$app->instance('config', $config = new Repository($items));

		if ( ! isset($loadedFromCache))
		{//没有加载cache文件，遍历config目录设置好所有配置
			$this->loadConfigurationFiles($app, $config);
		}
        //设置时区
		date_default_timezone_set($config['app.timezone']);
		mb_internal_encoding('UTF-8');
	}

	/**
	 * Load the configuration items from all of the files.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @param  \Illuminate\Contracts\Config\Repository  $config
	 * @return void
	 */
	protected function loadConfigurationFiles(Application $app, RepositoryContract $config)
	{
		foreach ($this->getConfigurationFiles($app) as $key => $path)
		{//遍历config目录，加载配置文件，文件名作为key，文件配置作为值
			$config->set($key, require $path);
		}
	}

	/**
	 * Get all of the configuration files for the application.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return array
	 */
	protected function getConfigurationFiles(Application $app)
	{
		$files = [];
		foreach (Finder::create()->files()->name('*.php')->in($app->configPath()) as $file)
		{//在config/目录下查找所有.php的文件
			$nesting = $this->getConfigurationNesting($file);
			//$files['app.log'] = app/log.php文件的内容  $files['abc.xyz.xxx'] = abc/xyz/xxx.php文件的内容
			$files[$nesting.basename($file->getRealPath(), '.php')] = $file->getRealPath();
		}

		return $files;
	}

	/**
	 * Get the configuration file nesting path.
	 *
	 * @param  \Symfony\Component\Finder\SplFileInfo  $file
	 * @return string
	 */
	private function getConfigurationNesting(SplFileInfo $file)
	{
		$directory = dirname($file->getRealPath());
		if ($tree = trim(str_replace(config_path(), '', $directory), DIRECTORY_SEPARATOR))
		{
			$tree = str_replace(DIRECTORY_SEPARATOR, '.', $tree).'.';// abc/xyz 替换为abc.xyz
		}
		return $tree;
	}

}
