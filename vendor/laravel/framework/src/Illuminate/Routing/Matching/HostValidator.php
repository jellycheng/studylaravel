<?php namespace Illuminate\Routing\Matching;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class HostValidator implements ValidatorInterface {

	/**
	 * Validate a given rule against a route and request.
	 * 当前请求对象的域名是否匹配
	 * @param  \Illuminate\Routing\Route  $route 路由对象
	 * @param  \Illuminate\Http\Request  $request 请求对象
	 * @return bool
	 */
	public function matches(Route $route, Request $request)
	{	//调用Symfony\Component\Routing\CompiledRoute类对象->getHostRegex() 获取域名正则,没有设置则为空
		if (is_null($route->getCompiled()->getHostRegex())) return true;

		return preg_match($route->getCompiled()->getHostRegex(), $request->getHost());//域名是否匹配
	}

}
