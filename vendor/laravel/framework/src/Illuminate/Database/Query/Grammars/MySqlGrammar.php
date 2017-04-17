<?php namespace Illuminate\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;

class MySqlGrammar extends Grammar {

	/**
	 * The components that make up a select clause.
	 * 拼接select语句的方法，注意数组key的顺序,配置的分别对应的方法是compile开头
	 * @var array
	 */
	protected $selectComponents = array(
		'aggregate',  //对应本类 compileAggregate方法，对应Illuminate\Database\Query\Builder类aggregate属性
		'columns',      //对应本类compileColumns方法， 对应Illuminate\Database\Query\Builder类columns属性
		'from',         //对应本类compileFrom方法，    对应Illuminate\Database\Query\Builder类from属性
		'joins',		//对应本类compileJoins方法，    对应Illuminate\Database\Query\Builder类joins属性
		'wheres',		//对应本类compileWheres方法，   对应Illuminate\Database\Query\Builder类wheres属性
		'groups',		//对应本类compileGroups方法，   对应Illuminate\Database\Query\Builder类groups属性
		'havings',		//对应本类compileHavings方法，  对应Illuminate\Database\Query\Builder类havings属性
		'orders',		//对应本类compileOrders方法，   对应Illuminate\Database\Query\Builder类orders属性
		'limit',		//对应本类compileLimit方法，    对应Illuminate\Database\Query\Builder类limit属性
		'offset',		//对应本类compileOffset方法，   对应Illuminate\Database\Query\Builder类offset属性
		'lock',			//对应本类compileLock方法，     对应Illuminate\Database\Query\Builder类lock属性
	);

	/**
	 * Compile a select query into SQL.
	 *
	 * @param  \Illuminate\Database\Query\Builder
	 * @return string
	 */
	public function compileSelect(Builder $query)
	{
		$sql = parent::compileSelect($query);

		if ($query->unions)
		{
			$sql = '('.$sql.') '.$this->compileUnions($query);
		}

		return $sql;
	}

	/**
	 * Compile a single union statement.
	 *
	 * @param  array  $union
	 * @return string
	 */
	protected function compileUnion(array $union)
	{
		$joiner = $union['all'] ? ' union all ' : ' union ';

		return $joiner.'('.$union['query']->toSql().')';
	}

	/**
	 * Compile the lock into SQL.
	 *
	 * @param  \Illuminate\Database\Query\Builder  $query
	 * @param  bool|string  $value
	 * @return string
	 */
	protected function compileLock(Builder $query, $value)
	{
		if (is_string($value)) return $value;

		return $value ? 'for update' : 'lock in share mode';
	}

	/**
	 * Compile an update statement into SQL.
	 *
	 * @param  \Illuminate\Database\Query\Builder  $query
	 * @param  array  $values
	 * @return string
	 */
	public function compileUpdate(Builder $query, $values)
	{
		$sql = parent::compileUpdate($query, $values);

		if (isset($query->orders))
		{
			$sql .= ' '.$this->compileOrders($query, $query->orders);
		}

		if (isset($query->limit))
		{
			$sql .= ' '.$this->compileLimit($query, $query->limit);
		}

		return rtrim($sql);
	}

	/**
	 * Compile a delete statement into SQL.
	 *
	 * @param  \Illuminate\Database\Query\Builder  $query
	 * @return string
	 */
	public function compileDelete(Builder $query)
	{
		$table = $this->wrapTable($query->from);

		$where = is_array($query->wheres) ? $this->compileWheres($query) : '';

		if (isset($query->joins))
		{
			$joins = ' '.$this->compileJoins($query, $query->joins);

			$sql = trim("delete $table from {$table}{$joins} $where");
		}
		else
		{
			$sql = trim("delete from $table $where");
		}

		if (isset($query->orders))
		{
			$sql .= ' '.$this->compileOrders($query, $query->orders);
		}

		if (isset($query->limit))
		{
			$sql .= ' '.$this->compileLimit($query, $query->limit);
		}

		return $sql;
	}

	/**
	 * Wrap a single string in keyword identifiers.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected function wrapValue($value)
	{
		if ($value === '*') return $value;

		return '`'.str_replace('`', '``', $value).'`';
	}

}
