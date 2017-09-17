<?php namespace Illuminate\Routing\Matching;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class SchemeValidator implements ValidatorInterface {

	/**
	 * Validate a given rule against a route and request.
	 *
	 * @param  \Illuminate\Routing\Route  $route 路由对象
	 * @param  \Illuminate\Http\Request  $request 请求对象
	 * @return bool
	 */
	public function matches(Route $route, Request $request)
	{
		if ($route->httpOnly())
		{//仅支持http协议请求
			return ! $request->secure();
		}
		elseif ($route->secure())
		{//仅支持https协议请求
			return $request->secure();
		}
		//不区分http/https协议
		return true;
	}

}
