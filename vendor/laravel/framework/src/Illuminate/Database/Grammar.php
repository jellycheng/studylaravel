<?php namespace Illuminate\Database;

//语法基类
abstract class Grammar {

	/**
	 * The grammar table prefix.
	 * 表前缀
	 * @var string
	 */
	protected $tablePrefix = '';

	/**
	 * Wrap an array of values.
	 *
	 * @param  array  $values
	 * @return array
	 */
	public function wrapArray(array $values)
	{
		return array_map(array($this, 'wrap'), $values);
	}

	/**
	 * Wrap a table in keyword identifiers.
	 *
	 * @param  string  $table
	 * @return string
	 */
	public function wrapTable($table)
	{
		if ($this->isExpression($table)) return $this->getValue($table);

		return $this->wrap($this->tablePrefix.$table, true);
	}

	/**
	 * Wrap a value in keyword identifiers.
	 *
	 * @param  string  $value
	 * @param  bool    $prefixAlias 别名是否加上表前缀，默认否
	 * @return string
	 */
	public function wrap($value, $prefixAlias = false)
	{
		if ($this->isExpression($value)) return $this->getValue($value); //获取表达式值

		if (strpos(strtolower($value), ' as ') !== false)
		{//值存在别名
			$segments = explode(' ', $value);// t_user as u

			if ($prefixAlias) $segments[2] = $this->tablePrefix.$segments[2];//别名加上表前缀

			return $this->wrap($segments[0]).' as '.$this->wrapValue($segments[2]);
		}
        //不存在as

		$wrapped = array();
		$segments = explode('.', $value);
		foreach ($segments as $key => $segment)
		{
			if ($key == 0 && count($segments) > 1)
			{
				$wrapped[] = $this->wrapTable($segment);
			}
			else
			{
				$wrapped[] = $this->wrapValue($segment);
			}
		}

		return implode('.', $wrapped);//用点合并字符串
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

		return '"'.str_replace('"', '""', $value).'"';
	}

	/**
	 * Convert an array of column names into a delimited string.
	 * 逗号拼接字段
	 * @param  array   $columns
	 * @return string
	 */
	public function columnize(array $columns)
	{
		return implode(', ', array_map(array($this, 'wrap'), $columns));
	}

	/**
	 * Create query parameter place-holders for an array.
	 * 批量获取值，然后用,逗号拼接
	 * @param  array   $values
	 * @return string
	 */
	public function parameterize(array $values)
	{
		return implode(', ', array_map(array($this, 'parameter'), $values));
	}

	/**
	 * Get the appropriate query parameter place-holder for a value.
	 * 如果是表达式则返回表达式值，否则返回?
	 * @param  mixed   $value
	 * @return string
	 */
	public function parameter($value)
	{
		return $this->isExpression($value) ? $this->getValue($value) : '?';
	}

	/**
	 * Get the value of a raw expression.
	 * 获取表达式值
	 * @param  \Illuminate\Database\Query\Expression  $expression
	 * @return string
	 */
	public function getValue($expression)
	{
		return $expression->getValue();
	}

	/**
	 * Determine if the given value is a raw expression.
	 * 是否是表达式
	 * @param  mixed  $value
	 * @return bool
	 */
	public function isExpression($value)
	{
		return $value instanceof Query\Expression;
	}

	/**
	 * Get the format for database stored dates.
	 * 日期时间格式
	 * @return string
	 */
	public function getDateFormat()
	{
		return 'Y-m-d H:i:s';
	}

	/**
	 * Get the grammar's table prefix.
	 * 获取表前缀
	 * @return string
	 */
	public function getTablePrefix()
	{
		return $this->tablePrefix;
	}

	/**
	 * Set the grammar's table prefix.
	 * 表前缀
	 * @param  string  $prefix
	 * @return $this
	 */
	public function setTablePrefix($prefix)
	{
		$this->tablePrefix = $prefix;

		return $this;
	}

}
