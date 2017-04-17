<?php namespace Illuminate\Database\Query;

use Closure;
use BadMethodCallException;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Processors\Processor;

class Builder {

	/**
	 * The database connection instance.
	 *
	 * @var \Illuminate\Database\Connection
	 */
	protected $connection;

	/**
	 * The database query grammar instance.
	 *
	 * @var \Illuminate\Database\Query\Grammars\Grammar
	 */
	protected $grammar;

	/**
	 * The database query post processor instance.
	 *
	 * @var \Illuminate\Database\Query\Processors\Processor
	 */
	protected $processor;

	/**
	 * The current query value bindings.
	 * 支持的绑定类型及配置
	 * @var array
	 */
	protected $bindings = array(
		'select' => [],
		'join'   => [],
		'where'  => [],
		'having' => [],
		'order'  => [],
	);

	/**
	 * An aggregate function and column to be run.
	 *
	 * @var array
	 */
	public $aggregate;

	/**
	 * The columns that should be returned.
	 * 查询要返回的字段
	 * @var array
	 */
	public $columns;

	/**
	 * Indicates if the query returns distinct results.
	 *
	 * @var bool
	 */
	public $distinct = false;

	/**
	 * The table which the query is targeting.
	 * 查询表名
	 * @var string
	 */
	public $from;

	/**
	 * The table joins for the query.
	 *
	 * @var array
	 */
	public $joins;

	/**
	 * The where constraints for the query.
	 *
	 * @var array
	 */
	public $wheres;

	/**
	 * The groupings for the query.
	 *
	 * @var array
	 */
	public $groups;

	/**
	 * The having constraints for the query.
	 *
	 * @var array
	 */
	public $havings;

	/**
	 * The orderings for the query.
	 *
	 * @var array
	 */
	public $orders;

	/**
	 * The maximum number of records to return.
	 *
	 * @var int
	 */
	public $limit;

	/**
	 * The number of records to skip.
	 *
	 * @var int
	 */
	public $offset;

	/**
	 * The query union statements.
	 *
	 * @var array
	 */
	public $unions;

	/**
	 * The maximum number of union records to return.
	 *
	 * @var int
	 */
	public $unionLimit;

	/**
	 * The number of union records to skip.
	 *
	 * @var int
	 */
	public $unionOffset;

	/**
	 * The orderings for the union query.
	 *
	 * @var array
	 */
	public $unionOrders;

	/**
	 * Indicates whether row locking is being used.
	 *
	 * @var string|bool
	 */
	public $lock;

	/**
	 * The field backups currently in use.
	 *
	 * @var array
	 */
	protected $backups = [];

	/**
	 * All of the available clause operators.
	 *
	 * @var array
	 */
	protected $operators = array(
		'=', '<', '>', '<=', '>=', '<>', '!=',
		'like', 'like binary', 'not like', 'between', 'ilike',
		'&', '|', '^', '<<', '>>',
		'rlike', 'regexp', 'not regexp',
		'~', '~*', '!~', '!~*', 'similar to',
                'not similar to',
	);

	/**
	 * Whether use write pdo for select.
	 *
	 * @var bool
	 */
	protected $useWritePdo = false;

	/**
	 * Create a new query builder instance.
	 *
	 * @param  \Illuminate\Database\ConnectionInterface  $connection 如Illuminate\Database\MySqlConnection类对象
	 * @param  \Illuminate\Database\Query\Grammars\Grammar  $grammar  如\Illuminate\Database\Query\Grammars\MySqlGrammar 类对象
	 * @param  \Illuminate\Database\Query\Processors\Processor  $processor 如 \Illuminate\Database\Query\Processors\MySqlProcessor类对象
	 * @return void
	 */
	public function __construct(ConnectionInterface $connection,
                                Grammar $grammar,
                                Processor $processor)
	{
		$this->grammar = $grammar;
		$this->processor = $processor;
		$this->connection = $connection;
	}

	/**
	 * Set the columns to be selected.
	 *
	 * @param  array  $columns
	 * @return $this
	 */
	public function select($columns = array('*'))
	{
		$this->columns = is_array($columns) ? $columns : func_get_args();

		return $this;
	}

	/**
	 * Add a new "raw" select expression to the query.
	 *
	 * @param  string  $expression
	 * @param  array   $bindings
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function selectRaw($expression, array $bindings = array())
	{
		$this->addSelect(new Expression($expression));
		if ($bindings)
		{
			$this->addBinding($bindings, 'select');
		}

		return $this;
	}

	/**
	 * Add a subselect expression to the query.
	 *
	 * @param  \Closure|\Illuminate\Database\Query\Builder|string $query
	 * @param  string  $as
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function selectSub($query, $as)
	{
		if ($query instanceof Closure)
		{
			$callback = $query;

			$callback($query = $this->newQuery());
		}

		if ($query instanceof Builder)
		{
			$bindings = $query->getBindings();

			$query = $query->toSql();
		}
		elseif (is_string($query))
		{
			$bindings = [];
		}
		else
		{
			throw new InvalidArgumentException;
		}

		return $this->selectRaw('('.$query.') as '.$this->grammar->wrap($as), $bindings);
	}

	/**
	 * Add a new select column to the query.
	 *
	 * @param  mixed  $column
	 * @return $this
	 */
	public function addSelect($column)
	{
		$column = is_array($column) ? $column : func_get_args();

		$this->columns = array_merge((array) $this->columns, $column);

		return $this;
	}

	/**
	 * Force the query to only return distinct results.
	 *
	 * @return $this
	 */
	public function distinct()
	{
		$this->distinct = true;

		return $this;
	}

	/**
	 * Set the table which the query is targeting.
	 * 注入表名
	 * @param  string  $table 表名
	 * @return $this
	 */
	public function from($table)
	{
		$this->from = $table;

		return $this;
	}

	/**
	 * Add a join clause to the query.
	 *
	 * @param  string  $table
	 * @param  string  $one
	 * @param  string  $operator
	 * @param  string  $two
	 * @param  string  $type
	 * @param  bool    $where
	 * @return $this
	 */
	public function join($table, $one, $operator = null, $two = null, $type = 'inner', $where = false)
	{
		// If the first "column" of the join is really a Closure instance the developer
		// is trying to build a join with a complex "on" clause containing more than
		// one condition, so we'll add the join and call a Closure with the query.
		if ($one instanceof Closure)
		{
			$this->joins[] = new JoinClause($type, $table);

			call_user_func($one, end($this->joins));
		}

		// If the column is simply a string, we can assume the join simply has a basic
		// "on" clause with a single condition. So we will just build the join with
		// this simple join clauses attached to it. There is not a join callback.
		else
		{
			$join = new JoinClause($type, $table);

			$this->joins[] = $join->on(
				$one, $operator, $two, 'and', $where
			);
		}

		return $this;
	}

	/**
	 * Add a "join where" clause to the query.
	 *
	 * @param  string  $table
	 * @param  string  $one
	 * @param  string  $operator
	 * @param  string  $two
	 * @param  string  $type
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function joinWhere($table, $one, $operator, $two, $type = 'inner')
	{
		return $this->join($table, $one, $operator, $two, $type, true);
	}

	/**
	 * Add a left join to the query.
	 *
	 * @param  string  $table
	 * @param  string  $first
	 * @param  string  $operator
	 * @param  string  $second
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function leftJoin($table, $first, $operator = null, $second = null)
	{
		return $this->join($table, $first, $operator, $second, 'left');
	}

	/**
	 * Add a "join where" clause to the query.
	 *
	 * @param  string  $table
	 * @param  string  $one
	 * @param  string  $operator
	 * @param  string  $two
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function leftJoinWhere($table, $one, $operator, $two)
	{
		return $this->joinWhere($table, $one, $operator, $two, 'left');
	}

	/**
	 * Add a right join to the query.
	 *
	 * @param  string  $table
	 * @param  string  $first
	 * @param  string  $operator
	 * @param  string  $second
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function rightJoin($table, $first, $operator = null, $second = null)
	{
		return $this->join($table, $first, $operator, $second, 'right');
	}

	/**
	 * Add a "right join where" clause to the query.
	 *
	 * @param  string  $table
	 * @param  string  $one
	 * @param  string  $operator
	 * @param  string  $two
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function rightJoinWhere($table, $one, $operator, $two)
	{
		return $this->joinWhere($table, $one, $operator, $two, 'right');
	}

	/**
	 * Add a basic where clause to the query.
	 * 返回本类对象
	 * @param  string  $column 字段名或['字段名'=>'字段值1','字段名'=>'字段值2']
	 * @param  string  $operator 只有当第1个参数是字段名字符串时才有用，表示操作符如>,>=,<,<=,=
	 * @param  mixed   $value  字段值
	 * @param  string  $boolean   拼接条件 and，or 等
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function where($column, $operator = null, $value = null, $boolean = 'and')
	{
		//数组方式,$colum=['字段名'=>'字段值1','字段名'=>'字段值2']
		if (is_array($column))
		{
			return $this->whereNested(function($query) use ($column)
			{
				foreach ($column as $key => $value)
				{
					$query->where($key, '=', $value);
				}
			}, $boolean);
		}

		//只有2个参数，则说明第1个参数是字段名，第2个参数是字段值
		if (func_num_args() == 2)
		{
			list($value, $operator) = array($operator, '=');
		}
		elseif ($this->invalidOperatorAndValue($operator, $value))
		{
			throw new InvalidArgumentException("Value must be provided.");
		}

		//字段名是闭包
		if ($column instanceof Closure)
		{
			return $this->whereNested($column, $boolean);
		}

		if ( ! in_array(strtolower($operator), $this->operators, true))
		{
			list($value, $operator) = array($operator, '=');
		}

		//字段值是闭包
		if ($value instanceof Closure)
		{
			return $this->whereSub($column, $operator, $value, $boolean);
		}

		//字段值为null
		if (is_null($value))
		{
			return $this->whereNull($column, $boolean, $operator != '=');
		}
		$type = 'Basic';
		$this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');
		if ( ! $value instanceof Expression)
		{//值不是表达式 则设置bindings属性值
			$this->addBinding($value, 'where');
		}
		return $this;
	}

	/**
	 * Add an "or where" clause to the query.
	 *
	 * @param  string  $column
	 * @param  string  $operator
	 * @param  mixed   $value
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function orWhere($column, $operator = null, $value = null)
	{
		return $this->where($column, $operator, $value, 'or');
	}

	/**
	 * Determine if the given operator and value combination is legal.
	 *
	 * @param  string  $operator
	 * @param  mixed  $value
	 * @return bool
	 */
	protected function invalidOperatorAndValue($operator, $value)
	{
		$isOperator = in_array($operator, $this->operators);

		return $isOperator && $operator != '=' && is_null($value);
	}

	/**
	 * Add a raw where clause to the query.
	 *
	 * @param  string  $sql
	 * @param  array   $bindings
	 * @param  string  $boolean
	 * @return $this
	 */
	public function whereRaw($sql, array $bindings = array(), $boolean = 'and')
	{
		$type = 'raw';

		$this->wheres[] = compact('type', 'sql', 'boolean');

		$this->addBinding($bindings, 'where');

		return $this;
	}

	/**
	 * Add a raw or where clause to the query.
	 *
	 * @param  string  $sql
	 * @param  array   $bindings
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function orWhereRaw($sql, array $bindings = array())
	{
		return $this->whereRaw($sql, $bindings, 'or');
	}

	/**
	 * Add a where between statement to the query.
	 *
	 * @param  string  $column
	 * @param  array   $values
	 * @param  string  $boolean
	 * @param  bool  $not
	 * @return $this
	 */
	public function whereBetween($column, array $values, $boolean = 'and', $not = false)
	{
		$type = 'between';

		$this->wheres[] = compact('column', 'type', 'boolean', 'not');

		$this->addBinding($values, 'where');

		return $this;
	}

	/**
	 * Add an or where between statement to the query.
	 *
	 * @param  string  $column
	 * @param  array   $values
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function orWhereBetween($column, array $values)
	{
		return $this->whereBetween($column, $values, 'or');
	}

	/**
	 * Add a where not between statement to the query.
	 *
	 * @param  string  $column
	 * @param  array   $values
	 * @param  string  $boolean
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function whereNotBetween($column, array $values, $boolean = 'and')
	{
		return $this->whereBetween($column, $values, $boolean, true);
	}

	/**
	 * Add an or where not between statement to the query.
	 *
	 * @param  string  $column
	 * @param  array   $values
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function orWhereNotBetween($column, array $values)
	{
		return $this->whereNotBetween($column, $values, 'or');
	}

	/**
	 * Add a nested where statement to the query.
	 *
	 * @param  \Closure $callback
	 * @param  string   $boolean
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function whereNested(Closure $callback, $boolean = 'and')
	{
		// To handle nested queries we'll actually create a brand new query instance
		// and pass it off to the Closure that we have. The Closure can simply do
		// do whatever it wants to a query then we will store it for compiling.
		$query = $this->newQuery();

		$query->from($this->from);

		call_user_func($callback, $query);

		return $this->addNestedWhereQuery($query, $boolean);
	}

	/**
	 * Add another query builder as a nested where to the query builder.
	 *
	 * @param  \Illuminate\Database\Query\Builder|static $query
	 * @param  string  $boolean
	 * @return $this
	 */
	public function addNestedWhereQuery($query, $boolean = 'and')
	{
		if (count($query->wheres))
		{
			$type = 'Nested';

			$this->wheres[] = compact('type', 'query', 'boolean');

			$this->mergeBindings($query);
		}

		return $this;
	}

	/**
	 * Add a full sub-select to the query.
	 *
	 * @param  string   $column
	 * @param  string   $operator
	 * @param  \Closure $callback
	 * @param  string   $boolean
	 * @return $this
	 */
	protected function whereSub($column, $operator, Closure $callback, $boolean)
	{
		$type = 'Sub';

		$query = $this->newQuery();

		// Once we have the query instance we can simply execute it so it can add all
		// of the sub-select's conditions to itself, and then we can cache it off
		// in the array of where clauses for the "main" parent query instance.
		call_user_func($callback, $query);

		$this->wheres[] = compact('type', 'column', 'operator', 'query', 'boolean');

		$this->mergeBindings($query);

		return $this;
	}

	/**
	 * Add an exists clause to the query.
	 *
	 * @param  \Closure $callback
	 * @param  string   $boolean
	 * @param  bool     $not
	 * @return $this
	 */
	public function whereExists(Closure $callback, $boolean = 'and', $not = false)
	{
		$type = $not ? 'NotExists' : 'Exists';

		$query = $this->newQuery();

		// Similar to the sub-select clause, we will create a new query instance so
		// the developer may cleanly specify the entire exists query and we will
		// compile the whole thing in the grammar and insert it into the SQL.
		call_user_func($callback, $query);

		$this->wheres[] = compact('type', 'operator', 'query', 'boolean');

		$this->mergeBindings($query);

		return $this;
	}

	/**
	 * Add an or exists clause to the query.
	 *
	 * @param  \Closure $callback
	 * @param  bool     $not
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function orWhereExists(Closure $callback, $not = false)
	{
		return $this->whereExists($callback, 'or', $not);
	}

	/**
	 * Add a where not exists clause to the query.
	 *
	 * @param  \Closure $callback
	 * @param  string   $boolean
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function whereNotExists(Closure $callback, $boolean = 'and')
	{
		return $this->whereExists($callback, $boolean, true);
	}

	/**
	 * Add a where not exists clause to the query.
	 *
	 * @param  \Closure  $callback
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function orWhereNotExists(Closure $callback)
	{
		return $this->orWhereExists($callback, true);
	}

	/**
	 * Add a "where in" clause to the query.
	 *
	 * @param  string  $column
	 * @param  mixed   $values
	 * @param  string  $boolean
	 * @param  bool    $not
	 * @return $this
	 */
	public function whereIn($column, $values, $boolean = 'and', $not = false)
	{
		$type = $not ? 'NotIn' : 'In';

		// If the value of the where in clause is actually a Closure, we will assume that
		// the developer is using a full sub-select for this "in" statement, and will
		// execute those Closures, then we can re-construct the entire sub-selects.
		if ($values instanceof Closure)
		{
			return $this->whereInSub($column, $values, $boolean, $not);
		}

		$this->wheres[] = compact('type', 'column', 'values', 'boolean');

		$this->addBinding($values, 'where');

		return $this;
	}

	/**
	 * Add an "or where in" clause to the query.
	 *
	 * @param  string  $column
	 * @param  mixed   $values
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function orWhereIn($column, $values)
	{
		return $this->whereIn($column, $values, 'or');
	}

	/**
	 * Add a "where not in" clause to the query.
	 *
	 * @param  string  $column
	 * @param  mixed   $values
	 * @param  string  $boolean
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function whereNotIn($column, $values, $boolean = 'and')
	{
		return $this->whereIn($column, $values, $boolean, true);
	}

	/**
	 * Add an "or where not in" clause to the query.
	 *
	 * @param  string  $column
	 * @param  mixed   $values
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function orWhereNotIn($column, $values)
	{
		return $this->whereNotIn($column, $values, 'or');
	}

	/**
	 * Add a where in with a sub-select to the query.
	 *
	 * @param  string   $column
	 * @param  \Closure $callback
	 * @param  string   $boolean
	 * @param  bool     $not
	 * @return $this
	 */
	protected function whereInSub($column, Closure $callback, $boolean, $not)
	{
		$type = $not ? 'NotInSub' : 'InSub';

		// To create the exists sub-select, we will actually create a query and call the
		// provided callback with the query so the developer may set any of the query
		// conditions they want for the in clause, then we'll put it in this array.
		call_user_func($callback, $query = $this->newQuery());

		$this->wheres[] = compact('type', 'column', 'query', 'boolean');

		$this->mergeBindings($query);

		return $this;
	}

	/**
	 * Add a "where null" clause to the query.
	 *
	 * @param  string  $column
	 * @param  string  $boolean
	 * @param  bool    $not
	 * @return $this
	 */
	public function whereNull($column, $boolean = 'and', $not = false)
	{
		$type = $not ? 'NotNull' : 'Null';

		$this->wheres[] = compact('type', 'column', 'boolean');

		return $this;
	}

	/**
	 * Add an "or where null" clause to the query.
	 *
	 * @param  string  $column
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function orWhereNull($column)
	{
		return $this->whereNull($column, 'or');
	}

	/**
	 * Add a "where not null" clause to the query.
	 *
	 * @param  string  $column
	 * @param  string  $boolean
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function whereNotNull($column, $boolean = 'and')
	{
		return $this->whereNull($column, $boolean, true);
	}

	/**
	 * Add an "or where not null" clause to the query.
	 *
	 * @param  string  $column
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function orWhereNotNull($column)
	{
		return $this->whereNotNull($column, 'or');
	}

	/**
	 * Add a "where date" statement to the query.
	 *
	 * @param  string  $column
	 * @param  string   $operator
	 * @param  int   $value
	 * @param  string   $boolean
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function whereDate($column, $operator, $value, $boolean = 'and')
	{
		return $this->addDateBasedWhere('Date', $column, $operator, $value, $boolean);
	}

	/**
	 * Add a "where day" statement to the query.
	 *
	 * @param  string  $column
	 * @param  string   $operator
	 * @param  int   $value
	 * @param  string   $boolean
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function whereDay($column, $operator, $value, $boolean = 'and')
	{
		return $this->addDateBasedWhere('Day', $column, $operator, $value, $boolean);
	}

	/**
	 * Add a "where month" statement to the query.
	 *
	 * @param  string  $column
	 * @param  string   $operator
	 * @param  int   $value
	 * @param  string   $boolean
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function whereMonth($column, $operator, $value, $boolean = 'and')
	{
		return $this->addDateBasedWhere('Month', $column, $operator, $value, $boolean);
	}

	/**
	 * Add a "where year" statement to the query.
	 *
	 * @param  string  $column
	 * @param  string   $operator
	 * @param  int   $value
	 * @param  string   $boolean
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function whereYear($column, $operator, $value, $boolean = 'and')
	{
		return $this->addDateBasedWhere('Year', $column, $operator, $value, $boolean);
	}

	/**
	 * Add a date based (year, month, day) statement to the query.
	 *
	 * @param  string  $type
	 * @param  string  $column
	 * @param  string  $operator
	 * @param  int  $value
	 * @param  string  $boolean
	 * @return $this
	 */
	protected function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and')
	{
		$this->wheres[] = compact('column', 'type', 'boolean', 'operator', 'value');

		$this->addBinding($value, 'where');

		return $this;
	}

	/**
	 * Handles dynamic "where" clauses to the query.
	 *
	 * @param  string  $method  And字段名 或 Or字段名
	 * @param  string  $parameters  参数
	 * @return $this
	 */
	public function dynamicWhere($method, $parameters)
	{
		$finder = substr($method, 5);
		$segments = preg_split('/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE);
		//
		$connector = 'and'; //拼接符 如and or
		$index = 0;
		foreach ($segments as $segment)
		{
			//$segment=字段名
			if ($segment != 'And' && $segment != 'Or')
			{
				$this->addDynamic($segment, $connector, $parameters, $index);

				$index++;
			} else {
				$connector = $segment;
			}
		}

		return $this;
	}

	/**
	 * Add a single dynamic where clause statement to the query.
	 *
	 * @param  string  $segment 字段名
	 * @param  string  $connector  拼接符 如and or
	 * @param  array   $parameters  所有值
	 * @param  int     $index        取哪个值
	 * @return void
	 */
	protected function addDynamic($segment, $connector, $parameters, $index)
	{
		$bool = strtolower($connector);

		$this->where(snake_case($segment), '=', $parameters[$index], $bool);
	}

	/**
	 * Add a "group by" clause to the query.
	 *  orderBy('name', 'desc')， orderBy('desc')
	 * @param  array|string  $column,...
	 * @return $this
	 */
	public function groupBy()
	{
		foreach (func_get_args() as $arg)
		{
			$this->groups = array_merge((array) $this->groups, is_array($arg) ? $arg : [$arg]);
		}

		return $this;
	}

	/**
	 * Add a "having" clause to the query.
	 *  having('count', '>', 100)
	 * @param  string  $column
	 * @param  string  $operator
	 * @param  string  $value
	 * @param  string  $boolean
	 * @return $this
	 */
	public function having($column, $operator = null, $value = null, $boolean = 'and')
	{
		$type = 'basic';

		$this->havings[] = compact('type', 'column', 'operator', 'value', 'boolean');

		if ( ! $value instanceof Expression)
		{
			$this->addBinding($value, 'having');
		}

		return $this;
	}

	/**
	 * Add a "or having" clause to the query.
	 *
	 * @param  string  $column
	 * @param  string  $operator
	 * @param  string  $value
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function orHaving($column, $operator = null, $value = null)
	{
		return $this->having($column, $operator, $value, 'or');
	}

	/**
	 * Add a raw having clause to the query.
	 *
	 * @param  string  $sql
	 * @param  array   $bindings
	 * @param  string  $boolean
	 * @return $this
	 */
	public function havingRaw($sql, array $bindings = array(), $boolean = 'and')
	{
		$type = 'raw';

		$this->havings[] = compact('type', 'sql', 'boolean');

		$this->addBinding($bindings, 'having');

		return $this;
	}

	/**
	 * Add a raw or having clause to the query.
	 *
	 * @param  string  $sql
	 * @param  array   $bindings
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function orHavingRaw($sql, array $bindings = array())
	{
		return $this->havingRaw($sql, $bindings, 'or');
	}

	/**
	 * Add an "order by" clause to the query.
	 *
	 * @param  string  $column
	 * @param  string  $direction
	 * @return $this
	 */
	public function orderBy($column, $direction = 'asc')
	{
		$property = $this->unions ? 'unionOrders' : 'orders';
		$direction = strtolower($direction) == 'asc' ? 'asc' : 'desc';

		$this->{$property}[] = compact('column', 'direction');

		return $this;
	}

	/**
	 * Add an "order by" clause for a timestamp to the query.
	 *
	 * @param  string  $column
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function latest($column = 'created_at')
	{
		return $this->orderBy($column, 'desc');
	}

	/**
	 * Add an "order by" clause for a timestamp to the query.
	 *
	 * @param  string  $column
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function oldest($column = 'created_at')
	{
		return $this->orderBy($column, 'asc');
	}

	/**
	 * Add a raw "order by" clause to the query.
	 *
	 * @param  string  $sql
	 * @param  array  $bindings
	 * @return $this
	 */
	public function orderByRaw($sql, $bindings = array())
	{
		$type = 'raw';

		$this->orders[] = compact('type', 'sql');

		$this->addBinding($bindings, 'order');

		return $this;
	}

	/**
	 * Set the "offset" value of the query.
	 *
	 * @param  int  $value
	 * @return $this
	 */
	public function offset($value)
	{
		$property = $this->unions ? 'unionOffset' : 'offset';

		$this->$property = max(0, $value);

		return $this;
	}

	/**
	 * Alias to set the "offset" value of the query.
	 *
	 * @param  int  $value
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function skip($value)
	{
		return $this->offset($value);
	}

	/**
	 * Set the "limit" value of the query.
	 *
	 * @param  int  $value
	 * @return $this
	 */
	public function limit($value)
	{
		$property = $this->unions ? 'unionLimit' : 'limit';

		if ($value > 0) $this->$property = $value;

		return $this;
	}

	/**
	 * Alias to set the "limit" value of the query.
	 *
	 * @param  int  $value
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function take($value)
	{
		return $this->limit($value);
	}

	/**
	 * Set the limit and offset for a given page.
	 *
	 * @param  int  $page
	 * @param  int  $perPage
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function forPage($page, $perPage = 15)
	{
		return $this->skip(($page - 1) * $perPage)->take($perPage);
	}

	/**
	 * Add a union statement to the query.
	 *
	 * @param  \Illuminate\Database\Query\Builder|\Closure  $query
	 * @param  bool  $all
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function union($query, $all = false)
	{
		if ($query instanceof Closure)
		{
			call_user_func($query, $query = $this->newQuery());
		}

		$this->unions[] = compact('query', 'all');

		return $this->mergeBindings($query);
	}

	/**
	 * Add a union all statement to the query.
	 *
	 * @param  \Illuminate\Database\Query\Builder|\Closure  $query
	 * @return \Illuminate\Database\Query\Builder|static
	 */
	public function unionAll($query)
	{
		return $this->union($query, true);
	}

	/**
	 * Lock the selected rows in the table.
	 *
	 * @param  bool  $value
	 * @return $this
	 */
	public function lock($value = true)
	{
		$this->lock = $value;

		return $this;
	}

	/**
	 * Lock the selected rows in the table for updating.
	 *
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function lockForUpdate()
	{
		return $this->lock(true);
	}

	/**
	 * Share lock the selected rows in the table.
	 *
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function sharedLock()
	{
		return $this->lock(false);
	}

	/**
	 * Get the SQL representation of the query.
	 *
	 * @return string
	 */
	public function toSql()
	{
		return $this->grammar->compileSelect($this);
	}

	/**
	 * Execute a query for a single record by ID.
	 *
	 * @param  int    $id
	 * @param  array  $columns
	 * @return mixed|static
	 */
	public function find($id, $columns = array('*'))
	{
		return $this->where('id', '=', $id)->first($columns);
	}

	/**
	 * Pluck a single column's value from the first result of a query.
	 *
	 * @param  string  $column
	 * @return mixed
	 */
	public function pluck($column)
	{
		$result = (array) $this->first(array($column));

		return count($result) > 0 ? reset($result) : null;
	}

	/**
	 * Execute the query and get the first result.
	 *
	 * @param  array   $columns
	 * @return mixed|static
	 */
	public function first($columns = array('*'))
	{
		$results = $this->take(1)->get($columns);

		return count($results) > 0 ? reset($results) : null;
	}

	/**
	 * Execute the query as a "select" statement.
	 * 返回多条记录结果集
	 * @param  array  $columns
	 * @return array|static[]
	 */
	public function get($columns = array('*'))
	{
		return $this->getFresh($columns);
	}

	/**
	 * Execute the query as a fresh "select" statement.
	 * 返回多条记录结果集
	 * @param  array  $columns
	 * @return array|static[]
	 */
	public function getFresh($columns = array('*'))
	{
		if (is_null($this->columns)) $this->columns = $columns;

		return $this->processor->processSelect($this, $this->runSelect());
	}

	/**
	 * Run the query as a "select" statement against the connection.
	 * 执行select sql语句，并返回结果
	 * @return array
	 */
	protected function runSelect()
	{
		return $this->connection->select($this->toSql(), $this->getBindings(), ! $this->useWritePdo);
	}

	/**
	 * Paginate the given query into a simple paginator.
	 *
	 * @param  int  $perPage
	 * @param  array  $columns
	 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
	 */
	public function paginate($perPage = 15, $columns = ['*'])
	{
		$page = Paginator::resolveCurrentPage();

		$total = $this->getCountForPagination();

		$results = $this->forPage($page, $perPage)->get($columns);

		return new LengthAwarePaginator($results, $total, $perPage, $page, [
			'path' => Paginator::resolveCurrentPath()
		]);
	}

	/**
	 * Get a paginator only supporting simple next and previous links.
	 *
	 * This is more efficient on larger data-sets, etc.
	 *
	 * @param  int  $perPage
	 * @param  array  $columns
	 * @return \Illuminate\Contracts\Pagination\Paginator
	 */
	public function simplePaginate($perPage = 15, $columns = ['*'])
	{
		$page = Paginator::resolveCurrentPage();

		$this->skip(($page - 1) * $perPage)->take($perPage + 1);

		return new Paginator($this->get($columns), $perPage, $page, [
			'path' => Paginator::resolveCurrentPath()
		]);
	}

	/**
	 * Get the count of the total records for the paginator.
	 *
	 * @return int
	 */
	public function getCountForPagination()
	{
		$this->backupFieldsForCount();

		$total = $this->count();

		$this->restoreFieldsForCount();

		return $total;
	}

	/**
	 * Backup some fields for the pagination count.
	 *
	 * @return void
	 */
	protected function backupFieldsForCount()
	{
		foreach (['orders', 'limit', 'offset'] as $field)
		{
			$this->backups[$field] = $this->{$field};

			$this->{$field} = null;
		}
	}

	/**
	 * Restore some fields after the pagination count.
	 *
	 * @return void
	 */
	protected function restoreFieldsForCount()
	{
		foreach (['orders', 'limit', 'offset'] as $field)
		{
			$this->{$field} = $this->backups[$field];
		}

		$this->backups = [];
	}

	/**
	 * Chunk the results of the query.
	 *
	 * @param  int  $count
	 * @param  callable  $callback
	 * @return void
	 */
	public function chunk($count, callable $callback)
	{
		$results = $this->forPage($page = 1, $count)->get();

		while (count($results) > 0)
		{
			// On each chunk result set, we will pass them to the callback and then let the
			// developer take care of everything within the callback, which allows us to
			// keep the memory low for spinning through large result sets for working.
			if (call_user_func($callback, $results) === false)
			{
				break;
			}

			$page++;

			$results = $this->forPage($page, $count)->get();
		}
	}

	/**
	 * Get an array with the values of a given column.
	 *
	 * @param  string  $column
	 * @param  string  $key
	 * @return array
	 */
	public function lists($column, $key = null)
	{
		$columns = $this->getListSelect($column, $key);

		$results = new Collection($this->get($columns));

		return $results->lists($columns[0], array_get($columns, 1));
	}

	/**
	 * Get the columns that should be used in a list array.
	 *
	 * @param  string  $column
	 * @param  string  $key
	 * @return array
	 */
	protected function getListSelect($column, $key)
	{
		$select = is_null($key) ? array($column) : array($column, $key);

		// If the selected column contains a "dot", we will remove it so that the list
		// operation can run normally. Specifying the table is not needed, since we
		// really want the names of the columns as it is in this resulting array.
		return array_map(function($column)
		{
			$dot = strpos($column, '.');

			return $dot === false ? $column : substr($column, $dot + 1);
		}, $select);
	}

	/**
	 * Concatenate values of a given column as a string.
	 *
	 * @param  string  $column
	 * @param  string  $glue
	 * @return string
	 */
	public function implode($column, $glue = null)
	{
		if (is_null($glue)) return implode($this->lists($column));

		return implode($glue, $this->lists($column));
	}

	/**
	 * Determine if any rows exist for the current query.
	 *
	 * @return bool
	 */
	public function exists()
	{
		$limit = $this->limit;

		$result = $this->limit(1)->count() > 0;

		$this->limit($limit);

		return $result;
	}

	/**
	 * Retrieve the "count" result of the query.
	 *
	 * @param  string  $columns
	 * @return int
	 */
	public function count($columns = '*')
	{
		if ( ! is_array($columns))
		{
			$columns = array($columns);
		}

		return (int) $this->aggregate(__FUNCTION__, $columns);
	}

	/**
	 * Retrieve the minimum value of a given column.
	 *
	 * @param  string  $column
	 * @return mixed
	 */
	public function min($column)
	{
		return $this->aggregate(__FUNCTION__, array($column));
	}

	/**
	 * Retrieve the maximum value of a given column.
	 *
	 * @param  string  $column
	 * @return mixed
	 */
	public function max($column)
	{
		return $this->aggregate(__FUNCTION__, array($column));
	}

	/**
	 * Retrieve the sum of the values of a given column.
	 *
	 * @param  string  $column
	 * @return mixed
	 */
	public function sum($column)
	{
		$result = $this->aggregate(__FUNCTION__, array($column));

		return $result ?: 0;
	}

	/**
	 * Retrieve the average of the values of a given column.
	 *
	 * @param  string  $column
	 * @return mixed
	 */
	public function avg($column)
	{
		return $this->aggregate(__FUNCTION__, array($column));
	}

	/**
	 * Execute an aggregate function on the database.
	 *
	 * @param  string  $function
	 * @param  array   $columns
	 * @return mixed
	 */
	public function aggregate($function, $columns = array('*'))
	{
		$this->aggregate = compact('function', 'columns');

		$previousColumns = $this->columns;

		$results = $this->get($columns);

		$this->aggregate = null;

		$this->columns = $previousColumns;

		if (isset($results[0]))
		{
			$result = array_change_key_case((array) $results[0]);
			return $result['aggregate'];
		}
	}

	/**
	 * Insert a new record into the database.
	 *
	 * @param  array  $values
	 * @return bool
	 */
	public function insert(array $values)
	{
		if (empty($values)) return true;
		if ( ! is_array(reset($values)))
		{
			$values = array($values);
		}else
		{
			foreach ($values as $key => $value)
			{
				ksort($value); $values[$key] = $value;
			}
		}

		$bindings = array();

		foreach ($values as $record)
		{
			foreach ($record as $value)
			{
				$bindings[] = $value;
			}
		}

		$sql = $this->grammar->compileInsert($this, $values);

		$bindings = $this->cleanBindings($bindings);

		return $this->connection->insert($sql, $bindings);
	}

	/**
	 * Insert a new record and get the value of the primary key.
	 * 插入数据并返回自增id
	 * @param  array   $values = 【字段名=>字段值，字段名2=>值2】
	 * @param  string  $sequence 如果是mysql pdo 这个参数不用管，使用默认值即可
	 * @return int
	 */
	public function insertGetId(array $values, $sequence = null)
	{
		$sql = $this->grammar->compileInsertGetId($this, $values, $sequence);

		$values = $this->cleanBindings($values);//值不是表达式的一律做bind

		return $this->processor->processInsertGetId($this, $sql, $values, $sequence);
	}

	/**
	 * Update a record in the database.
	 *
	 * @param  array  $values
	 * @return int
	 */
	public function update(array $values)
	{
		$bindings = array_values(array_merge($values, $this->getBindings()));

		$sql = $this->grammar->compileUpdate($this, $values);

		return $this->connection->update($sql, $this->cleanBindings($bindings));
	}

	/**
	 * Increment a column's value by a given amount.
	 *
	 * @param  string  $column
	 * @param  int     $amount
	 * @param  array   $extra
	 * @return int
	 */
	public function increment($column, $amount = 1, array $extra = array())
	{
		$wrapped = $this->grammar->wrap($column);

		$columns = array_merge(array($column => $this->raw("$wrapped + $amount")), $extra);

		return $this->update($columns);
	}

	/**
	 * Decrement a column's value by a given amount.
	 *
	 * @param  string  $column
	 * @param  int     $amount
	 * @param  array   $extra
	 * @return int
	 */
	public function decrement($column, $amount = 1, array $extra = array())
	{
		$wrapped = $this->grammar->wrap($column);

		$columns = array_merge(array($column => $this->raw("$wrapped - $amount")), $extra);

		return $this->update($columns);
	}

	/**
	 * Delete a record from the database.
	 *
	 * @param  mixed  $id
	 * @return int
	 */
	public function delete($id = null)
	{
		// If an ID is passed to the method, we will set the where clause to check
		// the ID to allow developers to simply and quickly remove a single row
		// from their database without manually specifying the where clauses.
		if ( ! is_null($id)) $this->where('id', '=', $id);

		$sql = $this->grammar->compileDelete($this);

		return $this->connection->delete($sql, $this->getBindings());
	}

	/**
	 * Run a truncate statement on the table.
	 *
	 * @return void
	 */
	public function truncate()
	{
		foreach ($this->grammar->compileTruncate($this) as $sql => $bindings)
		{
			$this->connection->statement($sql, $bindings);
		}
	}

	/**
	 * Get a new instance of the query builder.
	 *
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function newQuery()
	{
		return new Builder($this->connection, $this->grammar, $this->processor);
	}

	/**
	 * Merge an array of where clauses and bindings.
	 *
	 * @param  array  $wheres
	 * @param  array  $bindings
	 * @return void
	 */
	public function mergeWheres($wheres, $bindings)
	{
		$this->wheres = array_merge((array) $this->wheres, (array) $wheres);

		$this->bindings['where'] = array_values(array_merge($this->bindings['where'], (array) $bindings));
	}

	/**
	 * Remove all of the expressions from a list of bindings.
	 * 获取值不是表达式的所有值
	 * @param  array  $bindings
	 * @return array
	 */
	protected function cleanBindings(array $bindings)
	{
		return array_values(array_filter($bindings, function($binding)
		{
			return ! $binding instanceof Expression;
		}));
	}

	/**
	 * Create a raw database expression.
	 *
	 * @param  mixed  $value
	 * @return \Illuminate\Database\Query\Expression
	 */
	public function raw($value)
	{
		return $this->connection->raw($value);
	}

	/**
	 * Get the current query value bindings in a flattened array.
	 *  函数将会把多维数组扁平化成一维
	 * @return array
	 */
	public function getBindings()
	{
		return array_flatten($this->bindings);
	}

	/**
	 * Get the raw array of bindings.
	 *
	 * @return array
	 */
	public function getRawBindings()
	{
		return $this->bindings;
	}

	/**
	 * Set the bindings on the query builder.
	 *
	 * @param  array   $bindings
	 * @param  string  $type
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setBindings(array $bindings, $type = 'where')
	{
		if ( ! array_key_exists($type, $this->bindings))
		{
			throw new InvalidArgumentException("Invalid binding type: {$type}.");
		}

		$this->bindings[$type] = $bindings;

		return $this;
	}

	/**
	 * Add a binding to the query.
	 * 设置bindings属性值
	 * @param  mixed   $value
	 * @param  string  $type
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addBinding($value, $type = 'where')
	{
		if ( ! array_key_exists($type, $this->bindings))
		{//不是合法的绑定类型 抛异常
			throw new InvalidArgumentException("Invalid binding type: {$type}.");
		}
		if (is_array($value))
		{
			$this->bindings[$type] = array_values(array_merge($this->bindings[$type], $value));
		} else {
			$this->bindings[$type][] = $value;
		}
		return $this;
	}

	/**
	 * Merge an array of bindings into our bindings.
	 *
	 * @param  \Illuminate\Database\Query\Builder  $query
	 * @return $this
	 */
	public function mergeBindings(Builder $query)
	{
		$this->bindings = array_merge_recursive($this->bindings, $query->bindings);

		return $this;
	}

	/**
	 * Get the database connection instance.
	 *
	 * @return \Illuminate\Database\ConnectionInterface
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * Get the database query processor instance.
	 *
	 * @return \Illuminate\Database\Query\Processors\Processor
	 */
	public function getProcessor()
	{
		return $this->processor;
	}

	/**
	 * Get the query grammar instance.
	 *
	 * @return \Illuminate\Database\Grammar
	 */
	public function getGrammar()
	{
		return $this->grammar;
	}

	/**
	 * Use the write pdo for query.
	 *
	 * @return $this
	 */
	public function useWritePdo()
	{
		$this->useWritePdo = true;

		return $this;
	}

	/**
	 * Handle dynamic method calls into the method.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call($method, $parameters)
	{
		if (starts_with($method, 'where'))
		{//调用了where开头不存在的方法
			return $this->dynamicWhere($method, $parameters);
		}

		$className = get_class($this);
        //抛异常，调用不存在的方法
		throw new BadMethodCallException("Call to undefined method {$className}::{$method}()");
	}

}
