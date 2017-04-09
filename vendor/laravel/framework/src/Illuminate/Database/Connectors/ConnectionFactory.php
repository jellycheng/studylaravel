<?php namespace Illuminate\Database\Connectors;

use PDO;
use InvalidArgumentException;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Contracts\Container\Container;

class ConnectionFactory {

	/**
	 * The IoC container instance.
	 *
	 * @var \Illuminate\Contracts\Container\Container
	 */
	protected $container;

	/**
	 * Create a new connection factory instance.
	 *
	 * @param  \Illuminate\Contracts\Container\Container  $container  app对象即容器对象
	 * @return void
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Establish a PDO connection based on the configuration.
	 *
	 * @param  array   $config
	 * @param  string  $name
	 * @return \Illuminate\Database\Connection 子类对象,如Illuminate\Database\MySqlConnection类对象
	 */
	public function make(array $config, $name = null)
	{
		$config = $this->parseConfig($config, $name);//完善配置
		if (isset($config['read']))
		{//存在读配置,则同时进行读写连接(进行了2次PDO连接)
			return $this->createReadWriteConnection($config);
		}
		//单个连接,即写连接
		return $this->createSingleConnection($config);
	}

	/**
	 * Create a single database connection instance.
	 * 进行一次PDO连接(不存在读写分离情况)
	 * @param  array  $config
	 * @return \Illuminate\Database\Connection 子类对象 如:Illuminate\Database\MySqlConnection类对象
	 */
	protected function createSingleConnection(array $config)
	{
		//调用Illuminate\Database\Connectors\MySqlConnector类对象->connect($config),返回PDO对象
		$pdo = $this->createConnector($config)->connect($config);//返回原始PDO对象

		return $this->createConnection($config['driver'], $pdo, $config['database'], $config['prefix'], $config);
	}

	/**
	 * Create a single database connection instance.
	 * 同时进行2次PDO连接(读写分离情况)
	 * 设置Illuminate\Database\MySqlConnection类对象readPdo属性=原始PDO对象
	 * @param  array  $config
	 * @return \Illuminate\Database\Connection 子类对象,如Illuminate\Database\MySqlConnection类对象
	 */
	protected function createReadWriteConnection(array $config)
	{
		//\Illuminate\Database\Connection子类对象,如:Illuminate\Database\MySqlConnection类对象
		$connection = $this->createSingleConnection($this->getWriteConfig($config));
		//设置\Illuminate\Database\MySqlConnection类对象readPdo属性=原始PDO对象
		return $connection->setReadPdo($this->createReadPdo($config));
	}

	/**
	 * Create a new PDO instance for reading.
	 * 通过db读配置返回原始PDO对象
	 * @param  array  $config
	 * @return \PDO
	 */
	protected function createReadPdo(array $config)
	{
		$readConfig = $this->getReadConfig($config);//获取读配置
		//调用Illuminate\Database\Connectors\MySqlConnector类对象->connect($readConfig);
		return $this->createConnector($readConfig)->connect($readConfig);
	}

	/**
	 * Get the read configuration for a read / write connection.
	 *
	 * @param  array  $config
	 * @return array
	 */
	protected function getReadConfig(array $config)
	{
		$readConfig = $this->getReadWriteConfig($config, 'read');

		return $this->mergeReadWriteConfig($config, $readConfig);
	}

	/**
	 * Get the read configuration for a read / write connection.
	 *
	 * @param  array  $config
	 * @return array
	 */
	protected function getWriteConfig(array $config)
	{
		$writeConfig = $this->getReadWriteConfig($config, 'write');

		return $this->mergeReadWriteConfig($config, $writeConfig);
	}

	/**
	 * Get a read / write level configuration.
	 *
	 * @param  array   $config
	 * @param  string  $type
	 * @return array
	 */
	protected function getReadWriteConfig(array $config, $type)
	{
		if (isset($config[$type][0]))
		{
			return $config[$type][array_rand($config[$type])];
		}

		return $config[$type];
	}

	/**
	 * Merge a configuration for a read / write connection.
	 *
	 * @param  array  $config
	 * @param  array  $merge
	 * @return array
	 */
	protected function mergeReadWriteConfig(array $config, array $merge)
	{
		return array_except(array_merge($config, $merge), array('read', 'write'));
	}

	/**
	 * Parse and prepare the database configuration.
	 * 完善配置
	 * @param  array   $config
	 * @param  string  $name
	 * @return array
	 */
	protected function parseConfig(array $config, $name)
	{
		return array_add(array_add($config, 'prefix', ''), 'name', $name);
	}

	/**
	 * Create a connector instance based on the configuration.
	 * 创建连接类对象,如返回Illuminate\Database\Connectors\MySqlConnector类对象
	 * @param  array  $config
	 * @return \Illuminate\Database\Connectors\ConnectorInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function createConnector(array $config)
	{
		if ( ! isset($config['driver']))
		{
			throw new InvalidArgumentException("A driver must be specified.");
		}

		if ($this->container->bound($key = "db.connector.{$config['driver']}"))
		{//存在绑定db.connector.mysql则直接实例化
			return $this->container->make($key);
		}

		switch ($config['driver'])
		{
			case 'mysql':
				//返回Illuminate\Database\Connectors\MySqlConnector类对象
				return new MySqlConnector;

			case 'pgsql':
				return new PostgresConnector;

			case 'sqlite':
				return new SQLiteConnector;

			case 'sqlsrv':
				return new SqlServerConnector;
		}

		throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]");
	}

	/**
	 * Create a new connection instance.
	 *
	 * @param  string   $driver 驱动名
	 * @param  \PDO     $connection =原始PDO对象
	 * @param  string   $database 库名
	 * @param  string   $prefix 表前缀
	 * @param  array    $config db配置
	 * @return \Illuminate\Database\Connection 子类对象 如:Illuminate\Database\MySqlConnection类对象
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function createConnection($driver, PDO $connection, $database, $prefix = '', array $config = array())
	{
		if ($this->container->bound($key = "db.connection.{$driver}"))
		{//存在db.connection.mysql绑定则直接实例化
			return $this->container->make($key, array($connection, $database, $prefix, $config));
		}

		switch ($driver)
		{
			case 'mysql':
				return new MySqlConnection($connection, $database, $prefix, $config);

			case 'pgsql':
				return new PostgresConnection($connection, $database, $prefix, $config);

			case 'sqlite':
				return new SQLiteConnection($connection, $database, $prefix, $config);

			case 'sqlsrv':
				return new SqlServerConnection($connection, $database, $prefix, $config);
		}

		throw new InvalidArgumentException("Unsupported driver [$driver]");
	}

}
