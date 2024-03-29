<?php namespace Illuminate\Database\Eloquent;

use Illuminate\Contracts\Queue\EntityNotFoundException;
use Illuminate\Contracts\Queue\EntityResolver as EntityResolverContract;

class QueueEntityResolver implements EntityResolverContract {

	/**
	 * Resolve the entity for the given ID.
	 * 按id查询一条记录
	 * @param  string  $type = 类名
	 * @param  mixed  $id  记录id
	 * @return mixed
	 */
	public function resolve($type, $id)
	{
		$instance = (new $type)->find($id);

		if ($instance)
		{
			return $instance;
		}

		throw new EntityNotFoundException($type, $id);
	}

}
