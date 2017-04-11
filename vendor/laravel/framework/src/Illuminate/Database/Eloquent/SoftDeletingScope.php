<?php namespace Illuminate\Database\Eloquent;

class SoftDeletingScope implements ScopeInterface {

	/**
	 * All of the extensions to be added to the builder.
	 *  扩展，追加Builder类的宏方法
	 * @var array
	 */
	protected $extensions = ['ForceDelete', 'Restore', 'WithTrashed', 'OnlyTrashed'];

	/**
	 * Apply the scope to a given Eloquent query builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @return void
	 */
	public function apply(Builder $builder, Model $model)
	{	/**getQualifiedDeletedAtColumn()方法在Illuminate\Database\Eloquent\SoftDeletes中定义 返回 "表名.删除时间字段"
	\Illuminate\Database\Eloquent\Builder对象->__call('whereNull', '表名.删除时间字段');=>Illuminate\Database\Query\Builder->whereNull('表名.删除时间字段','and',false);
	 	*/
		$builder->whereNull($model->getQualifiedDeletedAtColumn());//设置Illuminate\Database\Query\Builder对象的wheres属性

		$this->extend($builder);
	}

	/**
	 * Remove the scope from the given Eloquent query builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @return void
	 */
	public function remove(Builder $builder, Model $model)
	{
		$column = $model->getQualifiedDeletedAtColumn();

		$query = $builder->getQuery();

		$query->wheres = collect($query->wheres)->reject(function($where) use ($column)
		{
			return $this->isSoftDeleteConstraint($where, $column);
		})->values()->all();
	}

	/**
	 * Extend the query builder with the needed functions.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @return void
	 */
	public function extend(Builder $builder)
	{
		foreach ($this->extensions as $extension)
		{	//追加宏方法
			$this->{"add{$extension}"}($builder);
		}
		//设置Builder类的onDelete属性
		$builder->onDelete(function(Builder $builder)
		{
			$column = $this->getDeletedAtColumn($builder);

			return $builder->update(array(
				$column => $builder->getModel()->freshTimestampString()
			));
		});
	}

	/**
	 * Get the "deleted at" column for the builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @return string
	 */
	protected function getDeletedAtColumn(Builder $builder)
	{
		if (count($builder->getQuery()->joins) > 0)
		{
			return $builder->getModel()->getQualifiedDeletedAtColumn();
		}
		else
		{
			return $builder->getModel()->getDeletedAtColumn();
		}
	}

	/**
	 * Add the force delete extension to the builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @return void
	 */
	protected function addForceDelete(Builder $builder)
	{
		$builder->macro('forceDelete', function(Builder $builder)
		{
			return $builder->getQuery()->delete();
		});
	}

	/**
	 * Add the restore extension to the builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @return void
	 */
	protected function addRestore(Builder $builder)
	{
		$builder->macro('restore', function(Builder $builder)
		{
			$builder->withTrashed();

			return $builder->update(array($builder->getModel()->getDeletedAtColumn() => null));
		});
	}

	/**
	 * Add the with-trashed extension to the builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @return void
	 */
	protected function addWithTrashed(Builder $builder)
	{
		$builder->macro('withTrashed', function(Builder $builder)
		{
			$this->remove($builder, $builder->getModel());

			return $builder;
		});
	}

	/**
	 * Add the only-trashed extension to the builder.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $builder
	 * @return void
	 */
	protected function addOnlyTrashed(Builder $builder)
	{
		$builder->macro('onlyTrashed', function(Builder $builder)
		{
			$model = $builder->getModel();

			$this->remove($builder, $model);

			$builder->getQuery()->whereNotNull($model->getQualifiedDeletedAtColumn());

			return $builder;
		});
	}

	/**
	 * Determine if the given where clause is a soft delete constraint.
	 *
	 * @param  array   $where
	 * @param  string  $column
	 * @return bool
	 */
	protected function isSoftDeleteConstraint(array $where, $column)
	{
		return $where['type'] == 'Null' && $where['column'] == $column;
	}

}
