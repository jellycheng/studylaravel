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
	 * @var array
	 */
	protected $routes = array();

	/**
	 * An flattened array of all of the routes.
	 *
	 * @var array
	 */
	protected $allRoutes = array();

	/**
	 * A look-up table of routes by their names.
	 *
	 * @var array
	 */
	protected $nameList = array();

	/**
	 * A look-up table of routes by controller action.
	 *
	 * @var array
	 */
	protected $actionList = array();

	/**
	 * Add a Route instance to the collection.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @return \Illuminate\Routing\Route
	 */
	public function add(Route $route)
	{
		$this->addToCollections($route);

		$this->addLookups($route);

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
		$domainAndUri = $route->domain().$route->getUri();

		foreach ($route->methods() as $method)
		{
			$this->routes[$method][$domainAndUri] = $route;
		}

		$this->allRoutes[$method.$domainAndUri] = $route;
	}

	/**
	 * Add the route to any look-up tables if necessary.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @return void
	 */
	protected function addLookups($route)
	{
		// If the route has a name, we will add it to the name look-up table so that we
		// will quickly be able to find any route associate with a name and not have
		// to iterate through every route every time we need to perform a look-up.
		$action = $route->getAction();

		if (isset($action['as']))
		{
			$this->nameList[$action['as']] = $route;
		}

		// When the route is routing to a controller we will also store the action that
		// is used by the route. This will let us reverse route to controllers while
		// processing a request and easily generate URLs to the given controllers.
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
	 * 匹配路由
	 * @param  \Illuminate\Http\Request  $request 请求对象
	 * @return \Illuminate\Routing\Route
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function match(Request $request)
	{
		$routes = $this->get($request->getMethod());//根据请求方式，获取所有路由

		$route = $this->check($routes, $request);//返回匹配的Route对象，没有返回null

		if ( ! is_null($route))
		{//已匹配，存在Route类对象
			return $route->bind($request);//返回Route类对象
		}

		// If no route was found, we will check if a matching is route is specified on
		// another HTTP verb. If it is we will need to throw a MethodNotAllowed and
		// inform the user agent of which HTTP verb it should use for this route.
		$others = $this->checkForAlternateVerbs($request);

		if (count($others) > 0)
		{
			return $this->getRouteForMethods($request, $others);
		}
        //没有匹配路由抛异常
		throw new NotFoundHttpException;
	}

	/**
	 * Determine if any routes match on another HTTP verb.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	protected function checkForAlternateVerbs($request)
	{
		$methods = array_diff(Router::$verbs, array($request->getMethod()));

		// Here we will spin through all verbs except for the current request verb and
		// check to see if any routes respond to them. If they do, we will return a
		// proper error response with the correct headers on the response string.
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
		{
			return (new Route('OPTIONS', $request->path(), function() use ($methods)
			{
				return new Response('', 200, array('Allow' => implode(',', $methods)));

			}))->bind($request);
		}

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
	 * @param  array  $routes  路由数组
	 * @param  \Illuminate\http\Request  $request 请求对象
	 * @param  bool  $includingMethod
	 * @return \Illuminate\Routing\Route|null
	 */
	protected function check(array $routes, $request, $includingMethod = true)
	{
		return array_first($routes, function($key, $value) use ($request, $includingMethod)
		{
			return $value->matches($request, $includingMethod);
		});
	}

	/**
	 * Get all of the routes in the collection.
	 *
	 * @param  string|null  $method
	 * @return array
	 */
	protected function get($method = null)
	{
		if (is_null($method)) return $this->getRoutes();

		return array_get($this->routes, $method, array());
	}

	/**
	 * Determine if the route collection contains a given named route.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function hasNamedRoute($name)
	{
		return ! is_null($this->getByName($name));
	}

	/**
	 * Get a route instance by its name.
	 *
	 * @param  string  $name
	 * @return \Illuminate\Routing\Route|null
	 */
	public function getByName($name)
	{
		return isset($this->nameList[$name]) ? $this->nameList[$name] : null;
	}

	/**
	 * Get a route instance by its controller action.
	 *
	 * @param  string  $action
	 * @return \Illuminate\Routing\Route|null
	 */
	public function getByAction($action)
	{
		return isset($this->actionList[$action]) ? $this->actionList[$action] : null;
	}

	/**
	 * Get all of the routes in the collection.
	 *
	 * @return array
	 */
	public function getRoutes()
	{
		return array_values($this->allRoutes);
	}

	/**
	 * Get an iterator for the items.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->getRoutes());
	}

	/**
	 * Count the number of items in the collection.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->getRoutes());
	}

}
