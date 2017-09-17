<?php namespace Illuminate\Routing\Matching;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class UriValidator implements ValidatorInterface {

	/**
	 * Validate a given rule against a route and request.
	 * 当前请求对象的uri是否匹配
	 * @param  \Illuminate\Routing\Route  $route 路由对象
	 * @param  \Illuminate\Http\Request  $request 请求对象
	 * @return bool
	 */
	public function matches(Route $route, Request $request)
	{
		$path = $request->path() == '/' ? '/' : '/'.$request->path();
		//调用Symfony\Component\Routing\CompiledRoute类对象->getRegex() 返回uri正则
		return preg_match($route->getCompiled()->getRegex(), rawurldecode($path));
	}

}
