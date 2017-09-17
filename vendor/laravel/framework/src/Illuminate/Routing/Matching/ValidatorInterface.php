<?php namespace Illuminate\Routing\Matching;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

interface ValidatorInterface {

	/**
	 * Validate a given rule against a route and request.
	 *
	 * @param  \Illuminate\Routing\Route  $route 路由对象
	 * @param  \Illuminate\Http\Request  $request 请求对象
	 * @return bool
	 */
	public function matches(Route $route, Request $request);

}
