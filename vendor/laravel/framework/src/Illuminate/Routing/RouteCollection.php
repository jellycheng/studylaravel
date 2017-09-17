<?php namespace Illuminate\Routing;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class RouteCollection implements Countable, IteratorAggregate {

	/**
	 * An array of the routes keyed by method.
	 *
	 * @var array = [ '请求方式如GET'=>['域名+uri'=>路由对象, '域名+uri2'=>路由对象2, ], 'POST'=>['域名+uri'=>路由对象, '域名+uri2'=>路由对象2, ]]
	 */
	protected $routes = array();

	/**
	 * An flattened array of all of the routes.
	 *
	 * @var array = ['请求方式+域名+uri']=路由对象
	 */
	protected $allRoutes = array();

	/**
	 * A look-up table of routes by their names.
	 *
	 * @var array = ['action的as名'=>路由对象, ]
	 */
	protected $nameList = array();

	/**
	 * A look-up table of routes by controller action.
	 *
	 * @var array = ['action的controller值'=>路由对象, ]
	 */
	protected $actionList = array();

	/**
	 * Add a Route instance to the collection.
	 * 路由集合中添加路由对象
	 * @param  \Illuminate\Routing\Route  $route 对象
	 * @return \Illuminate\Routing\Route
	 */
	public function add(Route $route)
	{
		$this->addToCollections($route);//设置routes和allRoutes属性值
		$this->addLookups($route);//设置nameList和actionList属性值
		return $route;
	}

	/**
	 * Add the given route to the arrays of routes.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @return void
	 */
	protected function addToCollections($route)
	{
		$domainAndUri = $route->domain().$route->getUri();//域名+uri

		foreach ($route->methods() as $method)
		{//路由对象支持的请求方式
			$this->routes[$method][$domainAndUri] = $route;//['请求方式如GET'=>[['域名+uri']=>路由对象],['域名+uri2']=>路由对象2] ]
		}

		$this->allRoutes[$method.$domainAndUri] = $route;//['请求方式+域名+uri']=路由对象
	}

	/**
	 * Add the route to any look-up tables if necessary.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @return void
	 */
	protected function addLookups($route)
	{
		// 获取路由对象的action属性值
		$action = $route->getAction();

		if (isset($action['as']))
		{
			$this->nameList[$action['as']] = $route;
		}

		if (isset($action['controller']))
		{
			$this->addToActionList($action, $route);
		}
	}

	/**
	 * Add a route to the controller action dictionary.
	 *
	 * @param  array  $action
	 * @param  \Illuminate\Routing\Route  $route
	 * @return void
	 */
	protected function addToActionList($action, $route)
	{
		$this->actionList[$action['controller']] = $route;
	}

	/**
	 * Find the first route matching a given request.
	 * 匹配路由,返回匹配的路由对象否则抛异常
	 * @param  \Illuminate\Http\Request  $request 请求对象
	 * @return \Illuminate\Routing\Route
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function match(Request $request)
	{
		$routes = $this->get($request->getMethod());//根据请求方式，获取该请求方式下所有路由对象

		$route = $this->check($routes, $request);//返回匹配的Route对象，没有返回null

		if ( ! is_null($route))
		{//已匹配，存在Route类对象
			return $route->bind($request);//先进行变量参数处理,然后返回Route类对象
		}

		//未匹配,检测并返回其它请求方式匹配的对应类方法,没检测到返回空数组
		$others = $this->checkForAlternateVerbs($request);

		if (count($others) > 0)
		{//存在其它请求方式匹配的对应类方法
			return $this->getRouteForMethods($request, $others);
		}
        //没有匹配路由抛异常
		throw new NotFoundHttpException;
	}

	/**
	 * Determine if any routes match on another HTTP verb.
	 * 返回其它请求方式匹配的对应类方法
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	protected function checkForAlternateVerbs($request)
	{
		$methods = array_diff(Router::$verbs, array($request->getMethod()));//返回差集(即不是当前请求方式的所有请求方式)

		//存其它请求方式匹配的对应类方法
		$others = array();

		foreach ($methods as $method)
		{
			if ( ! is_null($this->check($this->get($method), $request, false)))
			{
				$others[] = $method;
			}
		}

		return $others;
	}

	/**
	 * Get a route (if necessary) that responds when other available methods are present.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  array  $methods
	 * @return \Illuminate\Routing\Route
	 *
	 * @throws \Symfony\Component\Routing\Exception\MethodNotAllowedHttpException
	 */
	protected function getRouteForMethods($request, array $methods)
	{
		if ($request->method() == 'OPTIONS')
		{//跨域请求
			return (new Route('OPTIONS', $request->path(), function() use ($methods)
			{
				return new Response('', 200, array('Allow' => implode(',', $methods)));

			}))->bind($request);
		}
		//抛异常
		$this->methodNotAllowed($methods);
	}

	/**
	 * Throw a method not allowed HTTP exception.
	 *
	 * @param  array  $others
	 * @return void
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
	 */
	protected function methodNotAllowed(array $others)
	{
		throw new MethodNotAllowedHttpException($others);
	}

	/**
	 * Determine if a route in the array matches the request.
	 * 返回匹配的Route对象
	 * @param  array  $routes  [路由对象1, 路由对象N]
	 * @param  \Illuminate\http\Request  $request 请求对象
	 * @param  bool  $includingMethod
	 * @return \Illuminate\Routing\Route|null
	 */
	protected function check(array $routes, $request, $includingMethod = true)
	{
		return array_first($routes, function($key, $value) use ($request, $includingMethod)
		{//执行每个路由对象的matches()方法,匹配返回true,不匹配返回false
			return $value->matches($request, $includingMethod);
		});
	}

	/**
	 * Get all of the routes in the collection.
	 *  获取该请求方式下所有路由对象
	 * @param  string|null  $method 请求方式
	 * @return array
	 */
	protected function get($method = null)
	{
		if (is_null($method)) return $this->getRoutes();

		return array_get($this->routes, $method, array());
	}

	/**
	 * Determine if the route collection contains a given named route.
	 * 是否存在
	 * @param  string  $name action的as名
	 * @return bool
	 */
	public function hasNamedRoute($name)
	{
		return ! is_null($this->getByName($name));
	}

	/**
	 * Get a route instance by its name.
	 *
	 * @param  string  $name action的as名
	 * @return \Illuminate\Routing\Route|null
	 */
	public function getByName($name)
	{
		return isset($this->nameList[$name]) ? $this->nameList[$name] : null;
	}

	/**
	 * Get a route instance by its controller action.
	 *
	 * @param  string  $action action的controller值
	 * @return \Illuminate\Routing\Route|null
	 */
	public function getByAction($action)
	{
		return isset($this->actionList[$action]) ? $this->actionList[$action] : null;
	}

	/**
	 * Get all of the routes in the collection.
	 * 获取所有路由对象
	 * @return array
	 */
	public function getRoutes()
	{
		return array_values($this->allRoutes);
	}

	/**
	 * Get an iterator for the items.
	 * 迭代器,用于foreach循环路由集合时,每个值都时路由对象
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->getRoutes());
	}

	/**
	 * Count the number of items in the collection.
	 * 路由对象个数
	 * @return int
	 */
	public function count()
	{
		return count($this->getRoutes());
	}

}
