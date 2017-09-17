<?php namespace Illuminate\Routing\Matching;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class MethodValidator implements ValidatorInterface {

	/**
	 * Validate a given rule against a route and request.
	 * 请求方式是否在路由对象中支持
	 * @param  \Illuminate\Routing\Route  $route 路由对象
	 * @param  \Illuminate\Http\Request  $request 请求对象
	 * @return bool
	 */
	public function matches(Route $route, Request $request)
	{
		return in_array($request->getMethod(), $route->methods());
	}

}
