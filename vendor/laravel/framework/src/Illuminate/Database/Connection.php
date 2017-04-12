<?php namespace Illuminate\Database;

use PDO;
use Closure;
use DateTime;
use Exception;
use LogicException;
use RuntimeException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Query\Processors\Processor;
use Doctrine\DBAL\Connection as DoctrineConnection;

//连接PDO之后的管理
class Connection implements ConnectionInterface {

	/**
	 * The active PDO connection.
	 * 原始PDO对象,是使用db的写配置生成的PDO对象
	 * @var PDO
	 */
	protected $pdo;

	/**
	 * The active PDO connection used for reads.
	 * 原始PDO对象,是使用db的读配置生成的PDO对象
	 * @var PDO
	 */
	protected $readPdo;

	/**
	 * The reconnector instance for the connection.
	 * 闭包,=注入连接回调方法(本类对象)
	 * @var callable
	 */
	protected $reconnector;

	/**
	 * The query grammar implementation.
	 *
	 * @var \Illuminate\Database\Query\Grammars\Grammar
	 */
	protected $queryGrammar;

	/**
	 * The schema grammar implementation.
	 *
	 * @var \Illuminate\Database\Schema\Grammars\Grammar
	 */
	protected $schemaGrammar;

	/**
	 * The query post processor implementation.
	 *
	 * @var \Illuminate\Database\Query\Processors\Processor
	 */
	protected $postProcessor;

	/**
	 * The event dispatcher instance.
	 *
	 * @var \Illuminate\Contracts\Events\Dispatcher
	 */
	protected $events;

	/**
	 * The default fetch mode of the connection.
	 *
	 * @var int
	 */
	protected $fetchMode = PDO::FETCH_ASSOC;

	/**
	 * The number of active transactions.
	 *
	 * @var int
	 */
	protected $transactions = 0;

	/**
	 * All of the queries run against the connection.
	 *
	 * @var array
	 */
	protected $queryLog = array();

	/**
	 * Indicates whether queries are being logged.
	 *
	 * @var bool
	 */
	protected $loggingQueries = false;

	/**
	 * Indicates if the connection is in a "dry run".
	 *
	 * @var bool
	 */
	protected $pretending = false;

	/**
	 * The name of the connected database.
	 *
	 * @var string
	 */
	protected $database;

	/**
	 * The table prefix for the connection.
	 *
	 * @var string
	 */
	protected $tablePrefix = '';

	/**
	 * The database connection configuration options.
	 * 数据库配置
	 * @var array
	 */
	protected $config = array();

	/**
	 * Create a new database connection instance.
	 *
	 * @param  \PDO     $pdo 原始pdo对象
	 * @param  string   $database 库名
	 * @param  string   $tablePrefix 表前缀
	 * @param  array    $config db配置
	 * @return void
	 */
	public function __construct(PDO $pdo, $database = '', $tablePrefix = '', array $config = array())
	{
		$this->pdo = $pdo; //原始PDO对象,是使用db的写配置生成的PDO对象
		$this->database = $database;
		$this->tablePrefix = $tablePrefix;
		$this->config = $config;
		//设置本类queryGrammar属性=\Illuminate\Database\Query\Grammars\Grammar 类对象 or \Illuminate\Database\Query\Grammars\MySqlGrammar 类对象
		$this->useDefaultQueryGrammar();
		//设置本类postProcessor属性=\Illuminate\Database\Query\Processors\Processor 类对象 or \Illuminate\Database\Query\Processors\MySqlProcessor类对象
		$this->useDefaultPostProcessor();
	}

	/**
	 * Set the query grammar to the default implementation.
	 * 设置本类queryGrammar属性=\Illuminate\Database\Query\Grammars\Grammar 类对象
	 * @return void
	 */
	public function useDefaultQueryGrammar()
	{
		$this->queryGrammar = $this->getDefaultQueryGrammar();
	}

	/**
	 * Get the default query grammar instance.
	 *
	 * @return \Illuminate\Database\Query\Grammars\Grammar 类对象
	 */
	protected function getDefaultQueryGrammar()
	{
		return new Query\Grammars\Grammar;
	}

	/**
	 * Set the schema grammar to the default implementation.
	 *
	 * @return void
	 */
	public function useDefaultSchemaGrammar()
	{
		$this->schemaGrammar = $this->getDefaultSchemaGrammar();
	}

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return \Illuminate\Database\Schema\Grammars\Grammar
	 */
	protected function getDefaultSchemaGrammar() {}

	/**
	 * Set the query post processor to the default implementation.
	 * 设置本类postProcessor属性=\Illuminate\Database\Query\Processors\Processor 类对象 or \Illuminate\Database\Query\Processors\MySqlProcessor 类对象
	 * @return void
	 */
	public function useDefaultPostProcessor()
	{
		$this->postProcessor = $this->getDefaultPostProcessor();
	}

	/**
	 * Get the default post processor instance.
	 *
	 * @return \Illuminate\Database\Query\Processors\Processor 类对象
	 */
	protected function getDefaultPostProcessor()
	{
		return new Query\Processors\Processor;
	}

	/**
	 * Get a schema builder instance for the connection.
	 *
	 * @return \Illuminate\Database\Schema\Builder 类对象
	 */
	public function getSchemaBuilder()
	{
		if (is_null($this->schemaGrammar)) { $this->useDefaultSchemaGrammar(); }

		return new Schema\Builder($this);
	}

	/**
	 * Begin a fluent query against a database table.
	 *
	 * @param  string  $table = 表名
	 * @return \Illuminate\Database\Query\Builder 类对象
	 */
	public function table($table)
	{
		$processor = $this->getPostProcessor();//\Illuminate\Database\Query\Processors\Processor 子类对象,如 \Illuminate\Database\Query\Processors\MySqlProcessor类对象
		//$this->getQueryGrammar()是\Illuminate\Database\Query\Grammars\Grammar or 子类对象 如：\Illuminate\Database\Query\Grammars\MySqlGrammar 类对象
		$query = new Query\Builder($this, $this->getQueryGrammar(), $processor);

		return $query->from($table);//注入表名，同时返回\Illuminate\Database\Query\Builder 类对象
	}

	/**
	 * Get a new raw query expression.
	 * 原始表达式,如DB::raw('count(*) as user_count, status')
	 * @param  mixed  $value
	 * @return \Illuminate\Database\Query\Expression 类对象
	 */
	public function raw($value)
	{
		return new Query\Expression($value);
	}

	/**
	 * Run a select statement and return a single result.
	 * 通过读pdo对象查询sql语句,返回一条记录
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return mixed
	 */
	public function selectOne($query, $bindings = array())
	{
		$records = $this->select($query, $bindings);

		return count($records) > 0 ? reset($records) : null;
	}

	/**
	 * Run a select statement against the database.
	 * 使用写pdo对象执行查询sql语句,返回查询结果
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return array
	 */
	public function selectFromWriteConnection($query, $bindings = array())
	{
		return $this->select($query, $bindings, false);
	}

	/**
	 * Run a select statement against the database.
	 * 执行查询sql语句,返回查询结果
	 * @param  string  $query
	 * @param  array  $bindings
	 * @param  bool  $useReadPdo= true获取读PDO对象,false获取写pdo对象
	 * @return array
	 */
	public function select($query, $bindings = array(), $useReadPdo = true)
	{
		return $this->run($query, $bindings, function($me, $query, $bindings) use ($useReadPdo)
		{
			if ($me->pretending()) return array();

			$statement = $this->getPdoForSelect($useReadPdo)->prepare($query);

			$statement->execute($me->prepareBindings($bindings));

			return $statement->fetchAll($me->getFetchMode());
		});
	}

	/**
	 * Get the PDO connection to use for a select query.
	 * 获取原始PDO对象
	 * @param  bool  $useReadPdo = true获取读PDO对象,false获取写pdo对象
	 * @return \PDO
	 */
	protected function getPdoForSelect($useReadPdo = true)
	{
		return $useReadPdo ? $this->getReadPdo() : $this->getPdo();
	}

	/**
	 * Run an insert statement against the database.
	 * 执行insert语句,返回bool值
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return bool
	 */
	public function insert($query, $bindings = array())
	{
		return $this->statement($query, $bindings);
	}

	/**
	 * Run an update statement against the database.
	 * 执行update语句,返回影响的记录数
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return int
	 */
	public function update($query, $bindings = array())
	{
		return $this->affectingStatement($query, $bindings);
	}

	/**
	 * Run a delete statement against the database.
	 * 执行删除sql语句,返回影响的记录数
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return int
	 */
	public function delete($query, $bindings = array())
	{
		return $this->affectingStatement($query, $bindings);
	}

	/**
	 * Execute an SQL statement and return the boolean result.
	 * 执行sql语句,返回bool值
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return bool
	 */
	public function statement($query, $bindings = array())
	{
		return $this->run($query, $bindings, function($me, $query, $bindings)
		{
			if ($me->pretending()) return true;

			$bindings = $me->prepareBindings($bindings);

			return $me->getPdo()->prepare($query)->execute($bindings);
		});
	}

	/**
	 * Run an SQL statement and get the number of rows affected.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return int
	 */
	public function affectingStatement($query, $bindings = array())
	{
		return $this->run($query, $bindings, function($me, $query, $bindings)
		{
			if ($me->pretending()) return 0;

			$statement = $me->getPdo()->prepare($query);

			$statement->execute($me->prepareBindings($bindings));

			return $statement->rowCount();//返回影响的记录数
		});
	}

	/**
	 * Run a raw, unprepared query against the PDO connection.
	 *
	 * @param  string  $query
	 * @return bool
	 */
	public function unprepared($query)
	{
		return $this->run($query, array(), function($me, $query)
		{
			if ($me->pretending()) return true;

			return (bool) $me->getPdo()->exec($query);
		});
	}

	/**
	 * Prepare the query bindings for execution.
	 *
	 * @param  array  $bindings
	 * @return array
	 */
	public function prepareBindings(array $bindings)
	{
		$grammar = $this->getQueryGrammar();

		foreach ($bindings as $key => $value)
		{
			//
			if ($value instanceof DateTime)
			{
				$bindings[$key] = $value->format($grammar->getDateFormat());
			}
			elseif ($value === false)
			{
				$bindings[$key] = 0;
			}
		}

		return $bindings;
	}

	/**
	 * Execute a Closure within a transaction.
	 * 执行事务
	 * @param  \Closure  $callback
	 * @return mixed
	 *
	 * @throws \Exception
	 */
	public function transaction(Closure $callback)
	{
		$this->beginTransaction();
		try
		{
			$result = $callback($this);

			$this->commit();
		} catch (Exception $e) {
			$this->rollBack();

			throw $e;
		}

		return $result;
	}

	/**
	 * Start a new database transaction.
	 * 开启事务
	 * @return void
	 */
	public function beginTransaction()
	{
		++$this->transactions;

		if ($this->transactions == 1)
		{
			$this->pdo->beginTransaction();
		}

		$this->fireConnectionEvent('beganTransaction');
	}

	/**
	 * Commit the active database transaction.
	 * 提交事务
	 * @return void
	 */
	public function commit()
	{
		if ($this->transactions == 1) $this->pdo->commit();

		--$this->transactions;

		$this->fireConnectionEvent('committed');
	}

	/**
	 * Rollback the active database transaction.
	 * 回滚
	 * @return void
	 */
	public function rollBack()
	{
		if ($this->transactions == 1)
		{
			$this->transactions = 0;

			$this->pdo->rollBack();
		}
		else
		{
			--$this->transactions;
		}

		$this->fireConnectionEvent('rollingBack');
	}

	/**
	 * Get the number of active transactions.
	 *
	 * @return int
	 */
	public function transactionLevel()
	{
		return $this->transactions;
	}

	/**
	 * Execute the given callback in "dry run" mode.
	 *
	 * @param  \Closure  $callback
	 * @return array
	 */
	public function pretend(Closure $callback)
	{
		$loggingQueries = $this->loggingQueries;

		$this->enableQueryLog();

		$this->pretending = true;

		$this->queryLog = [];

		// Basically to make the database connection "pretend", we will just return
		// the default values for all the query methods, then we will return an
		// array of queries that were "executed" within the Closure callback.
		$callback($this);

		$this->pretending = false;

		$this->loggingQueries = $loggingQueries;

		return $this->queryLog;
	}

	/**
	 * Run a SQL statement and log its execution context.
	 *
	 * @param  string    $query
	 * @param  array     $bindings
	 * @param  \Closure  $callback
	 * @return mixed
	 *
	 * @throws \Illuminate\Database\QueryException
	 */
	protected function run($query, $bindings, Closure $callback)
	{
		$this->reconnectIfMissingConnection();

		$start = microtime(true);

		try
		{
			$result = $this->runQueryCallback($query, $bindings, $callback);
		}
		catch (QueryException $e)
		{
			$result = $this->tryAgainIfCausedByLostConnection(
				$e, $query, $bindings, $callback
			);
		}

		//时间
		$time = $this->getElapsedTime($start);

		$this->logQuery($query, $bindings, $time);

		return $result;
	}

	/**
	 * Run a SQL statement.
	 *
	 * @param  string    $query
	 * @param  array     $bindings
	 * @param  \Closure  $callback
	 * @return mixed
	 *
	 * @throws \Illuminate\Database\QueryException
	 */
	protected function runQueryCallback($query, $bindings, Closure $callback)
	{
		try {
			$result = $callback($this, $query, $bindings);
		} catch (Exception $e) {
			throw new QueryException(
				$query, $this->prepareBindings($bindings), $e
			);
		}

		return $result;
	}

	/**
	 * Handle a query exception that occurred during query execution.
	 *
	 * @param  \Illuminate\Database\QueryException  $e
	 * @param  string    $query
	 * @param  array     $bindings
	 * @param  \Closure  $callback
	 * @return mixed
	 *
	 * @throws \Illuminate\Database\QueryException
	 */
	protected function tryAgainIfCausedByLostConnection(QueryException $e, $query, $bindings, Closure $callback)
	{
		if ($this->causedByLostConnection($e))
		{
			$this->reconnect();

			return $this->runQueryCallback($query, $bindings, $callback);
		}

		throw $e;
	}

	/**
	 * Determine if the given exception was caused by a lost connection.
	 *
	 * @param  \Illuminate\Database\QueryException  $e
	 * @return bool
	 */
	protected function causedByLostConnection(QueryException $e)
	{
		$message = $e->getPrevious()->getMessage();

		return str_contains($message, [
			'server has gone away',
			'no connection to the server',
			'Lost connection',
		]);
	}

	/**
	 * Disconnect from the underlying PDO connection.
	 * 断开连接
	 * @return void
	 */
	public function disconnect()
	{
		$this->setPdo(null)->setReadPdo(null);
	}

	/**
	 * Reconnect to the database.
	 * 重新连接
	 * @return void
	 *
	 * @throws \LogicException
	 */
	public function reconnect()
	{
		if (is_callable($this->reconnector))
		{
			return call_user_func($this->reconnector, $this);
		}

		throw new LogicException("Lost connection and no reconnector available.");
	}

	/**
	 * Reconnect to the database if a PDO connection is missing.
	 * 重新连接
	 * @return void
	 */
	protected function reconnectIfMissingConnection()
	{
		if (is_null($this->getPdo()) || is_null($this->getReadPdo()))
		{
			$this->reconnect();
		}
	}

	/**
	 * Log a query in the connection's query log.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @param  float|null  $time
	 * @return void
	 */
	public function logQuery($query, $bindings, $time = null)
	{
		if (isset($this->events))
		{//触发illuminate.query事件
			$this->events->fire('illuminate.query', array($query, $bindings, $time, $this->getName()));
		}

		if ( ! $this->loggingQueries) return;

		$this->queryLog[] = compact('query', 'bindings', 'time');
	}

	/**
	 * Register a database query listener with the connection.
	 * 监听事件
	 * @param  \Closure  $callback
	 * @return void
	 */
	public function listen(Closure $callback)
	{
		if (isset($this->events))
		{
			$this->events->listen('illuminate.query', $callback);
		}
	}

	/**
	 * Fire an event for this connection.
	 * 触发事件
	 * @param  string  $event
	 * @return void
	 */
	protected function fireConnectionEvent($event)
	{
		if (isset($this->events))
		{//connection.连接代号.事件名
			$this->events->fire('connection.'.$this->getName().'.'.$event, $this);
		}
	}

	/**
	 * Get the elapsed time since a given starting point.
	 *
	 * @param  int    $start
	 * @return float
	 */
	protected function getElapsedTime($start)
	{
		return round((microtime(true) - $start) * 1000, 2);
	}

	/**
	 * Get a Doctrine Schema Column instance.
	 *
	 * @param  string  $table
	 * @param  string  $column
	 * @return \Doctrine\DBAL\Schema\Column
	 */
	public function getDoctrineColumn($table, $column)
	{
		$schema = $this->getDoctrineSchemaManager();

		return $schema->listTableDetails($table)->getColumn($column);
	}

	/**
	 * Get the Doctrine DBAL schema manager for the connection.
	 *
	 * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
	 */
	public function getDoctrineSchemaManager()
	{
		return $this->getDoctrineDriver()->getSchemaManager($this->getDoctrineConnection());
	}

	/**
	 * Get the Doctrine DBAL database connection instance.
	 *
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getDoctrineConnection()
	{
		$driver = $this->getDoctrineDriver();

		$data = array('pdo' => $this->pdo, 'dbname' => $this->getConfig('database'));

		return new DoctrineConnection($data, $driver);
	}

	/**
	 * Get the current PDO connection.
	 *
	 * @return \PDO
	 */
	public function getPdo()
	{
		return $this->pdo;
	}

	/**
	 * Get the current PDO connection used for reading.
	 *
	 * @return \PDO
	 */
	public function getReadPdo()
	{
		if ($this->transactions >= 1) return $this->getPdo();

		return $this->readPdo ?: $this->pdo;
	}

	/**
	 * Set the PDO connection.
	 *
	 * @param  \PDO|null  $pdo
	 * @return $this
	 */
	public function setPdo($pdo)
	{
		if ($this->transactions >= 1)
			throw new RuntimeException("Can't swap PDO instance while within transaction.");

		$this->pdo = $pdo;

		return $this;
	}

	/**
	 * Set the PDO connection used for reading.
	 * 设置原始PDO对象,是使用db的读配置生成的PDO对象
	 * @param  \PDO|null  $pdo
	 * @return $this
	 */
	public function setReadPdo($pdo)
	{
		$this->readPdo = $pdo;
		return $this;
	}

	/**
	 * Set the reconnect instance on the connection.
	 * 注入重新连接回调方法
	 * @param  callable  $reconnector
	 * @return $this
	 */
	public function setReconnector(callable $reconnector)
	{
		$this->reconnector = $reconnector;

		return $this;
	}

	/**
	 * Get the database connection name.
	 * 获取连接代号
	 * @return string|null
	 */
	public function getName()
	{
		return $this->getConfig('name');
	}

	/**
	 * Get an option from the configuration options.
	 * 获取配置中指定的key值
	 * @param  string  $option
	 * @return mixed
	 */
	public function getConfig($option)
	{
		return array_get($this->config, $option);
	}

	/**
	 * Get the PDO driver name.
	 *
	 * @return string
	 */
	public function getDriverName()
	{
		return $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
	}

	/**
	 * Get the query grammar used by the connection.
	 *
	 * @return \Illuminate\Database\Query\Grammars\Grammar or 子类对象 如：\Illuminate\Database\Query\Grammars\MySqlGrammar 类对象
	 */
	public function getQueryGrammar()
	{
		return $this->queryGrammar;
	}

	/**
	 * Set the query grammar used by the connection.
	 *
	 * @param  \Illuminate\Database\Query\Grammars\Grammar
	 * @return void
	 */
	public function setQueryGrammar(Query\Grammars\Grammar $grammar)
	{
		$this->queryGrammar = $grammar;
	}

	/**
	 * Get the schema grammar used by the connection.
	 *
	 * @return \Illuminate\Database\Query\Grammars\Grammar
	 */
	public function getSchemaGrammar()
	{
		return $this->schemaGrammar;
	}

	/**
	 * Set the schema grammar used by the connection.
	 *
	 * @param  \Illuminate\Database\Schema\Grammars\Grammar
	 * @return void
	 */
	public function setSchemaGrammar(Schema\Grammars\Grammar $grammar)
	{
		$this->schemaGrammar = $grammar;
	}

	/**
	 * Get the query post processor used by the connection.
	 *
	 * @return \Illuminate\Database\Query\Processors\Processor
	 */
	public function getPostProcessor()
	{
		return $this->postProcessor;
	}

	/**
	 * Set the query post processor used by the connection.
	 *
	 * @param  \Illuminate\Database\Query\Processors\Processor
	 * @return void
	 */
	public function setPostProcessor(Processor $processor)
	{
		$this->postProcessor = $processor;
	}

	/**
	 * Get the event dispatcher used by the connection.
	 * 获取事件对象
	 * @return \Illuminate\Contracts\Events\Dispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->events;
	}

	/**
	 * Set the event dispatcher instance on the connection.
	 * 设置事件对象
	 * @param  \Illuminate\Contracts\Events\Dispatcher
	 * @return void
	 */
	public function setEventDispatcher(Dispatcher $events)
	{
		$this->events = $events;
	}

	/**
	 * Determine if the connection in a "dry run".
	 *
	 * @return bool
	 */
	public function pretending()
	{
		return $this->pretending === true;
	}

	/**
	 * Get the default fetch mode for the connection.
	 *
	 * @return int
	 */
	public function getFetchMode()
	{
		return $this->fetchMode;
	}

	/**
	 * Set the default fetch mode for the connection.
	 *
	 * @param  int  $fetchMode
	 * @return int
	 */
	public function setFetchMode($fetchMode)
	{
		$this->fetchMode = $fetchMode;
	}

	/**
	 * Get the connection query log.
	 *
	 * @return array
	 */
	public function getQueryLog()
	{
		return $this->queryLog;
	}

	/**
	 * Clear the query log.
	 *
	 * @return void
	 */
	public function flushQueryLog()
	{
		$this->queryLog = array();
	}

	/**
	 * Enable the query log on the connection.
	 * 开启查询日志sql
	 * @return void
	 */
	public function enableQueryLog()
	{
		$this->loggingQueries = true;
	}

	/**
	 * Disable the query log on the connection.
	 * 关闭查询日志sql
	 * @return void
	 */
	public function disableQueryLog()
	{
		$this->loggingQueries = false;
	}

	/**
	 * Determine whether we're logging queries.
	 * 获取查询日志sql状态
	 * @return bool
	 */
	public function logging()
	{
		return $this->loggingQueries;
	}

	/**
	 * Get the name of the connected database.
	 * 获取数据库名
	 * @return string
	 */
	public function getDatabaseName()
	{
		return $this->database;
	}

	/**
	 * Set the name of the connected database.
	 * 设置数据库名
	 * @param  string  $database
	 * @return string
	 */
	public function setDatabaseName($database)
	{
		$this->database = $database;
	}

	/**
	 * Get the table prefix for the connection.
	 * 获取表前缀
	 * @return string
	 */
	public function getTablePrefix()
	{
		return $this->tablePrefix;
	}

	/**
	 * Set the table prefix in use by the connection.
	 * 设置表前缀
	 * @param  string  $prefix
	 * @return void
	 */
	public function setTablePrefix($prefix)
	{
		$this->tablePrefix = $prefix;
		//\Illuminate\Database\Query\Grammars\Grammar or 子类对象 如：\Illuminate\Database\Query\Grammars\MySqlGrammar 类对象
		$this->getQueryGrammar()->setTablePrefix($prefix);
	}

	/**
	 * Set the table prefix and return the grammar.
	 * 设置表前缀并返回语法子类对象
	 * @param  \Illuminate\Database\Grammar  $grammar 子类对象
	 * @return \Illuminate\Database\Grammar
	 */
	public function withTablePrefix(Grammar $grammar)
	{
		$grammar->setTablePrefix($this->tablePrefix);//注入表前缀

		return $grammar;
	}

}
