<?php namespace Illuminate\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;

class MySqlGrammar extends Grammar {

	/**
	 * The components that make up a select clause.
	 * 拼接select语句的方法，注意数组key的顺序,配置的分别对应的方法是compile开头
	 * @var array
	 */
	protected $selectComponents = array(
		'aggregate',  //对应本类 compileAggregate方法，对应Illuminate\Database\Query\Builder类aggregate属性，拼接sql： select count(*) as aggregate , 其实就是聚合方法的sql拼接，如 count、max、min、avg 及 sum
		'columns',      //对应本类compileColumns方法， 对应Illuminate\Database\Query\Builder类columns属性,拼接sql：如果有拼接过aggregate属性则不需要拼接字段属性,否则返回select 字段名，字段名N  或者 select *
		'from',         //对应本类compileFrom方法，    对应Illuminate\Database\Query\Builder类from属性,拼接sql：返回 from 表名
		'joins',		//对应本类compileJoins方法，    对应Illuminate\Database\Query\Builder类joins属性,拼接sql：
		'wheres',		//对应本类compileWheres方法，   对应Illuminate\Database\Query\Builder类wheres属性,拼接sql：
		'groups',		//对应本类compileGroups方法，   对应Illuminate\Database\Query\Builder类groups属性,拼接sql： group by 字段名1，字段名N
		'havings',		//对应本类compileHavings方法，  对应Illuminate\Database\Query\Builder类havings属性,拼接sql：having count(字段)>2
		'orders',		//对应本类compileOrders方法，   对应Illuminate\Database\Query\Builder类orders属性,拼接sql：  order by 字段 asc
		'limit',		//对应本类compileLimit方法，    对应Illuminate\Database\Query\Builder类limit属性,拼接sql： limit 12
		'offset',		//对应本类compileOffset方法，   对应Illuminate\Database\Query\Builder类offset属性,拼接sql：  offset 122
		'lock',		//对应本类compileLock方法，     对应Illuminate\Database\Query\Builder类lock属性,拼接sql： for update 或 lock in share mode
	);

	/**
	 * Compile a select query into SQL.
	 * 拼接查询sql
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
	 * 拼接delete语句
	 * @param  \Illuminate\Database\Query\Builder  $query
	 * @return string
	 */
	public function compileDelete(Builder $query)
	{
		$table = $this->wrapTable($query->from);//表名

		$where = is_array($query->wheres) ? $this->compileWheres($query) : '';//拼接where条件

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
	 * 给值加上``
	 * @param  string  $value
	 * @return string
	 */
	protected function wrapValue($value)
	{
		if ($value === '*') return $value;

		return '`'.str_replace('`', '``', $value).'`';
	}

}
