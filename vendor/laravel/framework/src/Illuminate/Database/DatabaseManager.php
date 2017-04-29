<?php namespace Illuminate\Database;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Database\Connectors\ConnectionFactory;

class DatabaseManager implements ConnectionResolverInterface {

	/**
	 * The application instance.
	 * laravel的app对象
	 * @var \Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * The database connection factory instance.
	 *
	 * @var \Illuminate\Database\Connectors\ConnectionFactory
	 */
	protected $factory;

	/**
	 * The active connection instances.
	 * 存Illuminate\Database\MySqlConnection类对象
	 * @var array = ['连接代号'=>Illuminate\Database\MySqlConnection类对象, ]
	 */
	protected $connections = array();

	/**
	 * The custom connection resolvers.
	 *
	 * @var array
	 */
	protected $extensions = array();

	/**
	 * Create a new database manager instance.
	 *
	 * @param  \Illuminate\Foundation\Application  $app app对象
	 * @param  \Illuminate\Database\Connectors\ConnectionFactory  $factory 连接工厂类对象
	 * @return void
	 */
	public function __construct($app, ConnectionFactory $factory)
	{
		$this->app = $app;
		$this->factory = $factory;
	}

	/**
	 * Get a database connection instance.
	 * 一个连接代号对应一个Illuminate\Database\MySqlConnection类对象,起到间接单例作用
	 * @param  string  $name 连接代号,默认default
	 * @return \Illuminate\Database\Connection 子类对象,如Illuminate\Database\MySqlConnection类对象
	 */
	public function connection($name = null)
	{
		list($name, $type) = $this->parseConnectionName($name);

		if ( ! isset($this->connections[$name]))
		{	//Illuminate\Database\MySqlConnection 类对象
			$connection = $this->makeConnection($name);
			//容错
			$this->setPdoForType($connection, $type);
			//$this->connections[连接代号] = Illuminate\Database\MySqlConnection 类对象
			$this->connections[$name] = $this->prepare($connection);//做好类对象相关设置
		}

		return $this->connections[$name];
	}

	/**
	 * Parse the connection into an array of the name and read / write type.
	 *
	 * @param  string  $name = default, default::read, default::write
	 * @return array = ['default连接代号', null读写代号]
	 */
	protected function parseConnectionName($name)
	{
		$name = $name ?: $this->getDefaultConnection();//获取默认连接配置代号

		return Str::endsWith($name, ['::read', '::write'])
                            ? explode('::', $name, 2) : [$name, null];
	}

	/**
	 * Disconnect from the given database and remove from local cache.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function purge($name = null)
	{
		$this->disconnect($name);

		unset($this->connections[$name]);
	}

	/**
	 * Disconnect from the given database.
	 * 断开连接
	 * @param  string  $name
	 * @return void
	 */
	public function disconnect($name = null)
	{
		if (isset($this->connections[$name = $name ?: $this->getDefaultConnection()]))
		{	//把Illuminate\Database\MySqlConnection类的pdo和readPdo属性设为null
			$this->connections[$name]->disconnect();
		}
	}

	/**
	 * Reconnect to the given database.
	 * 根据代号,获取配置重新连接db
	 * @param  string  $name
	 * @return \Illuminate\Database\Connection 子类对象
	 */
	public function reconnect($name = null)
	{
		$this->disconnect($name = $name ?: $this->getDefaultConnection());

		if ( ! isset($this->connections[$name]))
		{//未连接,去连接,
			return $this->connection($name);
		}

		return $this->refreshPdoConnections($name);
	}

	/**
	 * Refresh the PDO connections on a given connection.
	 *
	 * @param  string  $name
	 * @return \Illuminate\Database\Connection 子类对象
	 */
	protected function refreshPdoConnections($name)
	{
		$fresh = $this->makeConnection($name);

		return $this->connections[$name]
                                ->setPdo($fresh->getPdo())
                                ->setReadPdo($fresh->getReadPdo());
	}

	/**
	 * Make the database connection instance.
	 *
	 * @param  string  $name
	 * @return \Illuminate\Database\Connection 子类对象,如Illuminate\Database\MySqlConnection类对象
	 */
	protected function makeConnection($name)
	{
		$config = $this->getConfig($name);//根据配置代号获取配置

		if (isset($this->extensions[$name]))
		{//配置代号存在扩展,调用扩展,扩展函数接收参数($config配置, $name连接代号)返回Illuminate\Database\MySqlConnection类对象
			return call_user_func($this->extensions[$name], $config, $name);
		}

		$driver = $config['driver'];//驱动名
		//驱动存在扩展,调用扩展
		if (isset($this->extensions[$driver]))
		{
			return call_user_func($this->extensions[$driver], $config, $name);
		}
		//调用连接工厂类的make方法(配置, 配置代号),返回Illuminate\Database\MySqlConnection类对象
		return $this->factory->make($config, $name);
	}

	/**
	 * Prepare the database connection instance.
	 *
	 * @param  \Illuminate\Database\Connection 子类对象  $connection
	 * @return \Illuminate\Database\Connection
	 */
	protected function prepare(Connection $connection)
	{
		//设置\Illuminate\Database\Connection 类对象fetchMode属性值
		$connection->setFetchMode($this->app['config']['database.fetch']);

		if ($this->app->bound('events'))
		{//存在事件对象,则为\Illuminate\Database\Connection 类对象注入事件对象
			$connection->setEventDispatcher($this->app['events']);
		}

		//为\Illuminate\Database\Connection 类对象注入重新连接回调方法
		$connection->setReconnector(function($connection)
		{
			$this->reconnect($connection->getName());//根据当前配置代号重新连接db
		});

		return $connection;
	}

	/**
	 * Prepare the read write mode for database connection instance.
	 * 如果存在预分配读写模式,则修复好读写属性
	 * @param  \Illuminate\Database\Connection 子类对象  $connection
	 * @param  string  $type = null|read|write
	 * @return \Illuminate\Database\Connection
	 */
	protected function setPdoForType(Connection $connection, $type = null)
	{
		if ($type == 'read')
		{	//设置Illuminate\Database\MySqlConnection类->pdo属性=原始PDO对象(读)
			$connection->setPdo($connection->getReadPdo());
		}
		elseif ($type == 'write')
		{	//设置Illuminate\Database\MySqlConnection类对象->readPdo属性=原始PDO对象(写)
			$connection->setReadPdo($connection->getPdo());
		}

		return $connection;
	}

	/**
	 * Get the configuration for a connection.
	 * 获取配置
	 * @param  string  $name
	 * @return array
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function getConfig($name)
	{
		$name = $name ?: $this->getDefaultConnection();

		//database.php的connections配置key值
		$connections = $this->app['config']['database.connections'];

		if (is_null($config = array_get($connections, $name)))
		{//获取配置内容,如果为null则抛异常
			throw new InvalidArgumentException("Database [$name] not configured.");
		}

		return $config;
	}

	/**
	 * Get the default connection name.
	 * 获取 database.php文件的default配置key
	 * @return string
	 */
	public function getDefaultConnection()
	{	//$this->app['config']=\Illuminate\Config\Repository类对象
		return $this->app['config']['database.default'];//获取Repository类items属性['database']['default']值
	}

	/**
	 * Set the default connection name.
	 * 设置默认连接代号
	 * @param  string  $name
	 * @return void
	 */
	public function setDefaultConnection($name)
	{	//设置Repository类items属性['database']['default']=$name
		$this->app['config']['database.default'] = $name;
	}

	/**
	 * Register an extension connection resolver.
	 * 设置连接代号扩展方法,
	 * @param  string    $name
	 * @param  callable  $resolver
	 * @return void
	 */
	public function extend($name, callable $resolver)
	{
		$this->extensions[$name] = $resolver;
	}

	/**
	 * Return all of the created connections.
	 * 获取all创建的\Illuminate\Database\Connection 类对象
	 * @return array
	 */
	public function getConnections()
	{
		return $this->connections;
	}

	/**
	 * Dynamically pass methods to the default connection.
	 * DatabaseManager类对象->方法();通过默认的db代号连接db,\Illuminate\Database\MySqlConnection类对象->$method($parameters...)
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->connection(), $method), $parameters);
	}

}
