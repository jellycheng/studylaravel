<?php namespace Illuminate\Database\Capsule;

use PDO;
use Illuminate\Container\Container;
use Illuminate\Database\DatabaseManager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Support\Traits\CapsuleManagerTrait;

/**
 * 本类使用例子:
 * use Illuminate\Database\Capsule\Manager as Capsule;
 * $capsule = new Capsule();//构造函数初始化app对象,默认配置,及设置\Illuminate\Database\DatabaseManager类对象等动作
 * $capsule->addConnection(db配置, 连接代号默认default);可以调用多次
 * $capsule->setEventDispatcher(事件对象); 本行调用可选
 * $capsule->setAsGlobal();//注入本类对象,使后续所有对象共用本类对象
 * $capsule->bootEloquent();
 * 至此,就可以使用以下方式调用
 * Illuminate\Database\Capsule\Manager::connection('连接代号')->\Illuminate\Database\MySqlConnection类方法();如Illuminate\Database\Capsule\Manager::connection('连接代号')->xyz(参数...);
 * Illuminate\Database\Capsule\Manager::table('表名')->\Illuminate\Database\MySqlConnection类对象table('表名');
 * Illuminate\Database\Capsule\Manager::xyz(参数...);调用调用Illuminate\Database\MySqlConnection类对象->xyz(参数...);
 *
 */
class Manager {

	use CapsuleManagerTrait;

	/**
	 * The database manager instance.
	 *
	 * @var \Illuminate\Database\DatabaseManager 类对象
	 */
	protected $manager;

	/**
	 * Create a new database capsule manager.
	 *
	 * @param  \Illuminate\Container\Container|null  $container
	 * @return void
	 */
	public function __construct(Container $container = null)
	{
		//设置app对象(容器对象),同时如果config未绑定则绑定一个config对象
		$this->setupContainer($container ?: new Container);

		//设置默认db配置
		$this->setupDefaultConfiguration();
		//设置manager属性=\Illuminate\Database\DatabaseManager 类对象
		$this->setupManager();
	}

	/**
	 * Setup the default database configuration options.
	 * 设置默认db配置
	 * @return void
	 */
	protected function setupDefaultConfiguration()
	{
		$this->container['config']['database.fetch'] = PDO::FETCH_ASSOC;

		$this->container['config']['database.default'] = 'default';//默认配置代号
	}

	/**
	 * Build the database manager instance.
	 * 设置manager属性=\Illuminate\Database\DatabaseManager 类对象
	 * @return void
	 */
	protected function setupManager()
	{
		$factory = new ConnectionFactory($this->container);

		$this->manager = new DatabaseManager($this->container, $factory);
	}

	/**
	 * Get a connection instance from the global manager.
	 *
	 * @param  string  $connection 连接代号
	 * @return \Illuminate\Database\Connection 子类对象
	 */
	public static function connection($connection = null)
	{
		return static::$instance->getConnection($connection);//调用本类的getConnection($connection)方法
	}

	/**
	 * Get a fluent query builder instance.
	 *
	 * @param  string  $table
	 * @param  string  $connection
	 * @return \Illuminate\Database\Query\Builder
	 */
	public static function table($table, $connection = null)
	{
		return static::$instance->connection($connection)->table($table);
	}

	/**
	 * Get a schema builder instance.
	 *
	 * @param  string  $connection
	 * @return \Illuminate\Database\Schema\Builder
	 */
	public static function schema($connection = null)
	{
		return static::$instance->connection($connection)->getSchemaBuilder();
	}

	/**
	 * Get a registered connection instance.
	 *
	 * @param  string  $name
	 * @return \Illuminate\Database\Connection 子类对象,如Illuminate\Database\MySqlConnection类对象
	 */
	public function getConnection($name = null)
	{
		return $this->manager->connection($name);
	}

	/**
	 * Register a connection with the manager.
	 * 新增db连接配置
	 * @param  array   $config db配置
	 * @param  string  $name 连接代号
	 * @return void
	 */
	public function addConnection(array $config, $name = 'default')
	{
		$connections = $this->container['config']['database.connections'];

		$connections[$name] = $config;

		$this->container['config']['database.connections'] = $connections;
	}

	/**
	 * Bootstrap Eloquent so it is ready for usage.
	 *
	 * @return void
	 */
	public function bootEloquent()
	{
		//给Model类注入\Illuminate\Database\DatabaseManager类对象
		Eloquent::setConnectionResolver($this->manager);

		//事件类对象注入
		if ($dispatcher = $this->getEventDispatcher())
		{//如果存在\Illuminate\Contracts\Events\Dispatcher类对象则注入Model类中
			Eloquent::setEventDispatcher($dispatcher);
		}
	}

	/**
	 * Set the fetch mode for the database connections.
	 *
	 * @param  int  $fetchMode
	 * @return $this
	 */
	public function setFetchMode($fetchMode)
	{
		$this->container['config']['database.fetch'] = $fetchMode;

		return $this;
	}

	/**
	 * Get the database manager instance.
	 *
	 * @return \Illuminate\Database\DatabaseManager 类对象
	 */
	public function getDatabaseManager()
	{
		return $this->manager;
	}

	/**
	 * Get the current event dispatcher instance.
	 *
	 * @return \Illuminate\Contracts\Events\Dispatcher 类对象
	 */
	public function getEventDispatcher()
	{
		if ($this->container->bound('events'))
		{
			return $this->container['events'];
		}
	}

	/**
	 * Set the event dispatcher instance to be used by connections.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $dispatcher
	 * @return void
	 */
	public function setEventDispatcher(Dispatcher $dispatcher)
	{
		$this->container->instance('events', $dispatcher);
	}

	/**
	 * Dynamically pass methods to the default connection.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public static function __callStatic($method, $parameters)
	{	//调用Illuminate\Database\MySqlConnection类对象->$method($parameters...);
		return call_user_func_array(array(static::connection(), $method), $parameters);
	}

}
