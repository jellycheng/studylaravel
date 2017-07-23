<?php namespace Illuminate\Foundation;
/** 
 * 单例 
 * 注册别名并设置自动加载器
 * \Illuminate\Foundation\AliasLoader::getInstance($appConfig['app.aliases'])->register();
*/
class AliasLoader {

	/**
	 * The array of class aliases.
	 *
	 * @var array
	 */
	protected $aliases;

	/**
	 * Indicates if a loader has been registered.
	 * 是否设置过别名类加载器
	 * @var bool
	 */
	protected $registered = false;

	/**
	 * The singleton instance of the loader.
	 *
	 * @var \Illuminate\Foundation\AliasLoader
	 */
	protected static $instance;

	/**
	 * Create a new AliasLoader instance.
	 *
	 * @param  array  $aliases
	 */
	private function __construct($aliases)
	{
		$this->aliases = $aliases;
	}

	/**
	 * Get or create the singleton alias loader instance.
	 *
	 * @param  array  $aliases
	 * @return \Illuminate\Foundation\AliasLoader
	 */
	public static function getInstance(array $aliases = array())
	{
		//单例
		if (is_null(static::$instance)) return static::$instance = new static($aliases);

		$aliases = array_merge(static::$instance->getAliases(), $aliases);

		static::$instance->setAliases($aliases);
		//返回当前类对象，可以做链式操作
		return static::$instance;
	}

	/**
	 * Load a class alias if it is registered.
	 * 加载器，使别名跟类名达到一样的效果 如'TestJelly1' => 'Illuminate\Support\Facades\TestJelly'
	 * @param  string  $alias=TestJelly1
	 * @return void
	 */
	public function load($alias)
	{
		if (isset($this->aliases[$alias]))
		{	//为类创建一个别名 bool class_alias(string $original原类名, string $alias别名 [, bool $autoload = TRUE如果类不存在则调用自动加载 ] )
			return class_alias($this->aliases[$alias], $alias);
		}
	}

	/**
	 * Add an alias to the loader.
	 *
	 * @param  string  $class
	 * @param  string  $alias
	 * @return void
	 */
	public function alias($class, $alias)
	{
		$this->aliases[$class] = $alias;
	}

	/**
	 * Register the loader on the auto-loader stack.
	 * 如果没设置则设置自动加载器
	 * @return void
	 */
	public function register()
	{
		if ( ! $this->registered)
		{
			$this->prependToLoaderStack();//自动加载器

			$this->registered = true;
		}
	}

	/**
	 * Prepend the load method to the auto-loader stack.
	 * 自动加载器
	 * @return void
	 */
	protected function prependToLoaderStack()
	{
		spl_autoload_register(array($this, 'load'), true, true);
	}

	/**
	 * Get the registered aliases.
	 *
	 * @return array
	 */
	public function getAliases()
	{
		return $this->aliases;
	}

	/**
	 * Set the registered aliases.
	 *
	 * @param  array  $aliases
	 * @return void
	 */
	public function setAliases(array $aliases)
	{
		$this->aliases = $aliases;
	}

	/**
	 * Indicates if the loader has been registered.
	 *
	 * @return bool
	 */
	public function isRegistered()
	{
		return $this->registered;
	}

	/**
	 * Set the "registered" state of the loader.
	 *
	 * @param  bool  $value
	 * @return void
	 */
	public function setRegistered($value)
	{
		$this->registered = $value;
	}

	/**
	 * Set the value of the singleton alias loader.
	 *
	 * @param  \Illuminate\Foundation\AliasLoader  $loader
	 * @return void
	 */
	public static function setInstance($loader)
	{
		static::$instance = $loader;
	}

	/**
	 * Clone method.
	 *
	 * @return void
	 */
	private function __clone()
	{
		//
	}

}
