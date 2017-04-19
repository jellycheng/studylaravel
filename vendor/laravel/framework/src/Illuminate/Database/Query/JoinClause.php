<?php namespace Illuminate\Database\Query;

/**
 * join查询对象
 */
class JoinClause {

	/**
	 * The type of join being performed.
	 * 类型，如inner、left、right
	 * @var string
	 */
	public $type;

	/**
	 * The table the join clause is joining to.
	 * 表名
	 * @var string
	 */
	public $table;

	/**
	 * The "on" clauses for the join.
	 *  ['first'=>'', 'operator'=>'is、or、and', 'second'=>'', 'boolean'=>'', 'where'=>'']
	 * @var array
	 */
	public $clauses = array();

	/**
	* The "on" bindings for the join.
	*
	* @var array =[值,值N]
	*/
	public $bindings = array();

	/**
	 * Create a new join clause instance.
	 * @param  string  $type 类型
	 * @param  string  $table 表名
	 * @return void
	 */
	public function __construct($type, $table)
	{
		$this->type = $type;
		$this->table = $table;
	}

	/**
	 * Add an "on" clause to the join.
	 *
	 * @param  string  $first
	 * @param  string  $operator
	 * @param  string  $second
	 * @param  string  $boolean
	 * @param  bool  $where
	 * @return $this
	 */
	public function on($first, $operator, $second, $boolean = 'and', $where = false)
	{
		$this->clauses[] = compact('first', 'operator', 'second', 'boolean', 'where');

		if ($where) $this->bindings[] = $second;

		return $this;
	}

	/**
	 * Add an "or on" clause to the join.
	 *
	 * @param  string  $first
	 * @param  string  $operator
	 * @param  string  $second
	 * @return \Illuminate\Database\Query\JoinClause
	 */
	public function orOn($first, $operator, $second)
	{
		return $this->on($first, $operator, $second, 'or');
	}

	/**
	 * Add an "on where" clause to the join.
	 *
	 * @param  string  $first
	 * @param  string  $operator
	 * @param  string  $second
	 * @param  string  $boolean
	 * @return \Illuminate\Database\Query\JoinClause
	 */
	public function where($first, $operator, $second, $boolean = 'and')
	{
		return $this->on($first, $operator, $second, $boolean, true);
	}

	/**
	 * Add an "or on where" clause to the join.
	 *
	 * @param  string  $first
	 * @param  string  $operator
	 * @param  string  $second
	 * @return \Illuminate\Database\Query\JoinClause
	 */
	public function orWhere($first, $operator, $second)
	{
		return $this->on($first, $operator, $second, 'or', true);
	}

	/**
	 * Add an "on where is null" clause to the join
	 *
	 * @param  string  $column
	 * @param  string  $boolean
	 * @return \Illuminate\Database\Query\JoinClause
	 */
	public function whereNull($column, $boolean = 'and')
	{
		return $this->on($column, 'is', new Expression('null'), $boolean, false);
	}

	/**
	 * Add an "or on where is null" clause to the join.
	 *
	 * @param  string  $column
	 * @return \Illuminate\Database\Query\JoinClause
	 */
	public function orWhereNull($column)
	{
		return $this->whereNull($column, 'or');
	}

	/**
	 * Add an "on where is not null" clause to the join
	 *
	 * @param  string  $column
	 * @param  string  $boolean
	 * @return \Illuminate\Database\Query\JoinClause
	 */
	public function whereNotNull($column, $boolean = 'and')
	{
		return $this->on($column, 'is', new Expression('not null'), $boolean, false);
	}

	/**
	 * Add an "or on where is not null" clause to the join.
	 *
	 * @param  string  $column
	 * @return \Illuminate\Database\Query\JoinClause
	 */
	public function orWhereNotNull($column)
	{
		return $this->whereNotNull($column, 'or');
	}

}
