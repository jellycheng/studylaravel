<?php namespace Illuminate\Routing;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Routing\Registrar as RegistrarContract;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Router implements RegistrarContract {

	use Macroable;

	/**
	 * The event dispatcher instance.
	 * 事件对象
	 * @var \Illuminate\Contracts\Events\Dispatcher
	 */
	protected $events;

	/**
	 * The IoC container instance.
	 * app容器对象
	 * @var \Illuminate\Container\Container
	 */
	protected $container;

	/**
	 * The route collection instance.
	 * 路由集合对象
	 * @var \Illuminate\Routing\RouteCollection
	 */
	protected $routes;

	/**
	 * The currently dispatched route instance.
	 * 当前匹配到的路由对象
	 * @var \Illuminate\Routing\Route
	 */
	protected $current;

	/**
	 * The request currently being dispatched.
	 * 当前请求对象
	 * @var \Illuminate\Http\Request
	 */
	protected $currentRequest;

	/**
	 * All of the short-hand keys for middlewares.
	 * 路由中间介
	 * @var array = ['中间件名1'=>'中间件类名1', '中间件名N'=>'中间件类名N','中间件名'=>'类名']
	 */
	protected $middleware = [];

	/**
	 * The registered pattern based filters.
	 *
	 * @var array
	 */
	protected $patternFilters = array();

	/**
	 * The registered regular expression based filters.
	 *
	 * @var array
	 */
	protected $regexFilters = array();

	/**
	 * The registered route value binders.
	 *
	 * @var array
	 */
	protected $binders = array();

	/**
	 * The globally available parameter patterns.
	 *
	 * @var array
	 */
	protected $patterns = array();

	/**
	 * The route group attribute stack.
	 * 路由组设置使用的变量栈
	 * @var array
	 */
	protected $groupStack = array();

	/**
	 * All of the verbs supported by the router.
	 *
	 * @var array
	 */
	public static $verbs = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS');

	/**
	 * Create a new Router instance.
	 * 创建路由管理者实例
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events Dispatcher类对象
	 * @param  \Illuminate\Container\Container  $container app类对象
	 * @return void
	 */
	public function __construct(Dispatcher $events, Container $container = null)
	{
		$this->events = $events;//事件对象
		$this->routes = new RouteCollection; //路由集合对象
		$this->container = $container ?: new Container;//app对象，容器对象
	}

	/**
	 * Register a new GET route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Illuminate\Routing\Route
	 */
	public function get($uri, $action)
	{
		return $this->addRoute(['GET', 'HEAD'], $uri, $action);
	}

	/**
	 * Register a new POST route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Illuminate\Routing\Route
	 */
	public function post($uri, $action)
	{
		return $this->addRoute('POST', $uri, $action);
	}

	/**
	 * Register a new PUT route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Illuminate\Routing\Route
	 */
	public function put($uri, $action)
	{
		return $this->addRoute('PUT', $uri, $action);
	}

	/**
	 * Register a new PATCH route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Illuminate\Routing\Route
	 */
	public function patch($uri, $action)
	{
		return $this->addRoute('PATCH', $uri, $action);
	}

	/**
	 * Register a new DELETE route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Illuminate\Routing\Route
	 */
	public function delete($uri, $action)
	{
		return $this->addRoute('DELETE', $uri, $action);
	}

	/**
	 * Register a new OPTIONS route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Illuminate\Routing\Route
	 */
	public function options($uri, $action)
	{
		return $this->addRoute('OPTIONS', $uri, $action);
	}

	/**
	 * Register a new route responding to all verbs.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action
	 * @return \Illuminate\Routing\Route
	 */
	public function any($uri, $action)
	{
		$verbs = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE');

		return $this->addRoute($verbs, $uri, $action);
	}

	/**
	 * Register a new route with the given verbs.
	 *
	 * @param  array|string  $methods 请求方式,如GET,POST ['GET', 'POST']
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action 动作
	 * @return \Illuminate\Routing\Route
	 */
	public function match($methods, $uri, $action)
	{
		return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
	}

	/**
	 * Register an array of controllers with wildcard routing.
	 *
	 * @param  array  $controllers
	 * @return void
	 */
	public function controllers(array $controllers)
	{
		foreach ($controllers as $uri => $name)
		{
			$this->controller($uri, $name);
		}
	}

	/**
	 * Route a controller to a URI with wildcard routing.
	 *
	 * @param  string  $uri
	 * @param  string  $controller 类名
	 * @param  array   $names
	 * @return void
	 */
	public function controller($uri, $controller, $names = array())
	{
		$prepended = $controller;

		//
		if ( ! empty($this->groupStack))
		{
			$prepended = $this->prependGroupUses($controller);
		}

		$routable = (new ControllerInspector)
							->getRoutable($prepended, $uri);

		//
		foreach ($routable as $method => $routes)
		{
			foreach ($routes as $route)
			{
				$this->registerInspected($route, $controller, $method, $names);
			}
		}

		$this->addFallthroughRoute($controller, $uri);
	}

	/**
	 * Register an inspected controller route.
	 *
	 * @param  array   $route
	 * @param  string  $controller
	 * @param  string  $method
	 * @param  array   $names
	 * @return void
	 */
	protected function registerInspected($route, $controller, $method, &$names)
	{
		$action = array('uses' => $controller.'@'.$method);

		//
		$action['as'] = array_get($names, $method);

		$this->{$route['verb']}($route['uri'], $action);
	}

	/**
	 * Add a fallthrough route for a controller.
	 *
	 * @param  string  $controller
	 * @param  string  $uri
	 * @return void
	 */
	protected function addFallthroughRoute($controller, $uri)
	{
		$missing = $this->any($uri.'/{_missing}', $controller.'@missingMethod');

		$missing->where('_missing', '(.*)');
	}

	/**
	 * Register an array of resource controllers.
	 *
	 * @param  array  $resources
	 * @return void
	 */
	public function resources(array $resources)
	{
		foreach ($resources as $name => $controller)
		{
			$this->resource($name, $controller);
		}
	}

	/**
	 * Route a resource to a controller.
	 *
	 * @param  string  $name
	 * @param  string  $controller
	 * @param  array   $options
	 * @return void
	 */
	public function resource($name, $controller, array $options = array())
	{
		(new ResourceRegistrar($this))->register($name, $controller, $options);
	}

	/**
	 * Create a route group with shared attributes.
	 *
	 * @param  array     $attributes
	 * @param  \Closure  $callback
	 * @return void
	 */
	public function group(array $attributes, Closure $callback)
	{
		$this->updateGroupStack($attributes);

		// Once we have updated the group stack, we will execute the user Closure and
		// merge in the groups attributes when the route is created. After we have
		// run the callback, we will pop the attributes off of this group stack.
		call_user_func($callback, $this);

		array_pop($this->groupStack);
	}

	/**
	 * Update the group stack with the given attributes.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	protected function updateGroupStack(array $attributes)
	{
		if ( ! empty($this->groupStack))
		{//不为空说明路由组存在嵌套
			$attributes = $this->mergeGroup($attributes, last($this->groupStack));
		}

		$this->groupStack[] = $attributes;
	}

	/**
	 * Merge the given array with the last group stack.
	 *
	 * @param  array  $new
	 * @return array
	 */
	public function mergeWithLastGroup($new)
	{
		return $this->mergeGroup($new, last($this->groupStack));
	}

	/**
	 * Merge the given group attributes.
	 *
	 * @param  array  $new
	 * @param  array  $old
	 * @return array
	 */
	public static function mergeGroup($new, $old)
	{
		$new['namespace'] = static::formatUsesPrefix($new, $old);

		$new['prefix'] = static::formatGroupPrefix($new, $old);

		if (isset($new['domain'])) unset($old['domain']);

		$new['where'] = array_merge(array_get($old, 'where', []), array_get($new, 'where', []));

		return array_merge_recursive(array_except($old, array('namespace', 'prefix', 'where')), $new);
	}

	/**
	 * Format the uses prefix for the new group attributes.
	 *
	 * @param  array  $new
	 * @param  array  $old
	 * @return string
	 */
	protected static function formatUsesPrefix($new, $old)
	{
		if (isset($new['namespace']) && isset($old['namespace']))
		{
			return trim(array_get($old, 'namespace'), '\\').'\\'.trim($new['namespace'], '\\');
		}
		elseif (isset($new['namespace']))
		{
			return trim($new['namespace'], '\\');
		}

		return array_get($old, 'namespace');
	}

	/**
	 * Format the prefix for the new group attributes.
	 *
	 * @param  array  $new
	 * @param  array  $old
	 * @return string
	 */
	protected static function formatGroupPrefix($new, $old)
	{
		if (isset($new['prefix']))
		{
			return trim(array_get($old, 'prefix'), '/').'/'.trim($new['prefix'], '/');
		}

		return array_get($old, 'prefix');
	}

	/**
	 * Get the prefix from the last group on the stack.
	 *
	 * @return string
	 */
	public function getLastGroupPrefix()
	{
		if ( ! empty($this->groupStack))
		{
			$last = end($this->groupStack);
			return isset($last['prefix']) ? $last['prefix'] : '';
		}

		return '';
	}

	/**
	 * Add a route to the underlying route collection.
	 * 向路由集合对象中添加路由对象
	 * @param  array|string  $methods 请求方式
	 * @param  string  $uri
	 * @param  \Closure|array|string  $action 动作
	 * @return \Illuminate\Routing\Route
	 */
	protected function addRoute($methods, $uri, $action)
	{
		return $this->routes->add($this->createRoute($methods, $uri, $action));
	}

	/**
	 * Create a new route instance.
	 *
	 * @param  array|string  $methods
	 * @param  string  $uri
	 * @param  mixed   $action
	 * @return \Illuminate\Routing\Route
	 */
	protected function createRoute($methods, $uri, $action)
	{

		if ($this->actionReferencesController($action))
		{//不是闭包,且是字符串 or 数组的uses key是字符串
			$action = $this->convertToControllerAction($action);//设置controller值=uses值
		}
		//实例化路由对象
		$route = $this->newRoute(
								$methods, $this->prefix($uri), $action
								);

		if ($this->hasGroupStack())
		{
			$this->mergeGroupAttributesIntoRoute($route);
		}

		$this->addWhereClausesToRoute($route);

		return $route;
	}

	/**
	 * Create a new Route object.
	 * 实例化路由类
	 * @param  array|string  $methods 请求方式
	 * @param  string  $uri
	 * @param  mixed   $action
	 * @return \Illuminate\Routing\Route
	 */
	protected function newRoute($methods, $uri, $action)
	{
		return (new Route($methods, $uri, $action))->setContainer($this->container);
	}

	/**
	 * Prefix the given URI with the last prefix.
	 *
	 * @param  string  $uri
	 * @return string
	 */
	protected function prefix($uri)
	{
		return trim(trim($this->getLastGroupPrefix(), '/').'/'.trim($uri, '/'), '/') ?: '/';
	}

	/**
	 * Add the necessary where clauses to the route based on its initial registration.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @return \Illuminate\Routing\Route
	 */
	protected function addWhereClausesToRoute($route)
	{
		$route->where(
			array_merge($this->patterns, array_get($route->getAction(), 'where', []))
		);

		return $route;
	}

	/**
	 * Merge the group stack with the controller action.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @return void
	 */
	protected function mergeGroupAttributesIntoRoute($route)
	{
		$action = $this->mergeWithLastGroup($route->getAction());

		$route->setAction($action);
	}

	/**
	 * Determine if the action is routing to a controller.
	 * action是否设置为控制器
	 * @param  array  $action = 闭包则返回false, 是字符串则返回true,数组中存在uses key且值是字符串则返回true
	 * @return bool
	 */
	protected function actionReferencesController($action)
	{
		if ($action instanceof Closure) return false;

		return is_string($action) || is_string(array_get($action, 'uses'));
	}

	/**
	 * Add a controller based route action to the action array.
	 * 把uses值复制给controller
	 * @param  array|string  $action
	 * @return array
	 */
	protected function convertToControllerAction($action)
	{
		if (is_string($action)) $action = array('uses' => $action);

		// Here we'll merge any group "uses" statement if necessary so that the action
		// has the proper clause for this property. Then we can simply set the name
		// of the controller on the action and return the action array for usage.
		if ( ! empty($this->groupStack))
		{
			$action['uses'] = $this->prependGroupUses($action['uses']);
		}

		//把uses值复制给controller
		$action['controller'] = $action['uses'];

		return $action;
	}

	/**
	 * Prepend the last group uses onto the use clause.
	 *
	 * @param  string  $uses
	 * @return string
	 */
	protected function prependGroupUses($uses)
	{
		$group = last($this->groupStack);

		return isset($group['namespace']) && strpos($uses, '\\') !== 0 ? $group['namespace'].'\\'.$uses : $uses;
	}

	/**
	 * Dispatch the request to the application.
	 * 查找匹配路由
	 * @param  \Illuminate\Http\Request  $request 请求对象
	 * @return \Illuminate\Http\Response 响应对象
	 */
	public function dispatch(Request $request)
	{
		$this->currentRequest = $request;//设置请求对象
		//
		$response = $this->callFilter('before', $request);
		if (is_null($response))
		{
			$response = $this->dispatchToRoute($request);
		}
		//处理相关响应头，返回响应对象
		$response = $this->prepareResponse($request, $response);
		$this->callFilter('after', $request, $response);
		return $response;
	}

	/**
	 * Dispatch the request to a route and return the response.
	 * 根据请求对象匹配查找匹配到的路由对象
	 * @param  \Illuminate\Http\Request  $request 请求对象
	 * @return mixed
	 */
	public function dispatchToRoute(Request $request)
	{
		//
		$route = $this->findRoute($request);//返回Route类对象
		$request->setRouteResolver(function() use ($route)
		{
			return $route;
		});

		$this->events->fire('router.matched', [$route, $request]);

		// Once we have successfully matched the incoming request to a given route we
		// can call the before filters on that route. This works similar to global
		// filters in that if a response is returned we will not call the route.
		$response = $this->callRouteBefore($route, $request);

		if (is_null($response))
		{
			$response = $this->runRouteWithinStack(
				$route, $request
			);
		}

		$response = $this->prepareResponse($request, $response);

		// After we have a prepared response from the route or filter we will call to
		// the "after" filters to do any last minute processing on this request or
		// response object before the response is returned back to the consumer.
		$this->callRouteAfter($route, $request, $response);

		return $response;
	}

	/**
	 * Run the given route within a Stack "onion" instance.
	 *
	 * @param  \Illuminate\Routing\Route  $route 匹配到的route路由对象
	 * @param  \Illuminate\Http\Request  $request 请求对象
	 * @return mixed
	 */
	protected function runRouteWithinStack(Route $route, Request $request)
	{
		$middleware = $this->gatherRouteMiddlewares($route);//该路由的中间件

		return (new Pipeline($this->container))
						->send($request)
						->through($middleware)
						->then(function($request) use ($route)
						{
							return $this->prepareResponse(
								$request,
								$route->run($request)  //执行匹配的路由对象run方法
							);
						});
	}

	/**
	 * Gather the middleware for the given route.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @return array
	 */
	public function gatherRouteMiddlewares(Route $route)
	{
		return Collection::make($route->middleware())->map(function($m)
		{
			return Collection::make(array_get($this->middleware, $m, $m));

		})->collapse()->all();
	}

	/**
	 * Find the route matching a given request.
	 * 查找匹配的路由
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Routing\Route
	 */
	protected function findRoute($request)
	{   //路由集合中匹配路由
		$this->current = $route = $this->routes->match($request);//返回匹配到的Route类对象,没匹配到则抛异常
		$this->container->instance('Illuminate\Routing\Route', $route);//注入app对象中
		return $this->substituteBindings($route);//返回Route类对象
	}

	/**
	 * Substitute the route bindings onto the route.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @return \Illuminate\Routing\Route
	 */
	protected function substituteBindings($route)
	{
		foreach ($route->parameters() as $key => $value)
		{
			if (isset($this->binders[$key]))
			{
				$route->setParameter($key, $this->performBinding($key, $value, $route));
			}
		}

		return $route;
	}

	/**
	 * Call the binding callback for the given key.
	 *
	 * @param  string  $key
	 * @param  string  $value
	 * @param  \Illuminate\Routing\Route  $route
	 * @return mixed
	 */
	protected function performBinding($key, $value, $route)
	{
		return call_user_func($this->binders[$key], $value, $route);
	}

	/**
	 * Register a route matched event listener.
	 *
	 * @param  string|callable  $callback
	 * @return void
	 */
	public function matched($callback)
	{
		$this->events->listen('router.matched', $callback);
	}

	/**
	 * Register a new "before" filter with the router.
	 *
	 * @param  string|callable  $callback
	 * @return void
	 */
	public function before($callback)
	{
		$this->addGlobalFilter('before', $callback);
	}

	/**
	 * Register a new "after" filter with the router.
	 *
	 * @param  string|callable  $callback
	 * @return void
	 */
	public function after($callback)
	{
		$this->addGlobalFilter('after', $callback);
	}

	/**
	 * Register a new global filter with the router.
	 *
	 * @param  string  $filter
	 * @param  string|callable   $callback
	 * @return void
	 */
	protected function addGlobalFilter($filter, $callback)
	{
		$this->events->listen('router.'.$filter, $this->parseFilter($callback));
	}

	/**
	 * Get all of the defined middleware short-hand names.
	 * 获取路由中间件配置
	 * @return array
	 */
	public function getMiddleware()
	{
		return $this->middleware;
	}

	/**
	 * Register a short-hand name for a middleware.
	 * 设置路由中间件
	 * @param  string  $name 中间件名
	 * @param  string  $class 类名
	 * @return $this
	 */
	public function middleware($name, $class)
	{
		$this->middleware[$name] = $class;

		return $this;
	}

	/**
	 * Register a new filter with the router.
	 *
	 * @param  string  $name
	 * @param  string|callable  $callback
	 * @return void
	 */
	public function filter($name, $callback)
	{
		$this->events->listen('router.filter: '.$name, $this->parseFilter($callback));
	}

	/**
	 * Parse the registered filter.
	 *
	 * @param  callable|string  $callback
	 * @return mixed
	 */
	protected function parseFilter($callback)
	{
		if (is_string($callback) && ! str_contains($callback, '@'))
		{
			return $callback.'@filter';
		}

		return $callback;
	}

	/**
	 * Register a pattern-based filter with the router.
	 *
	 * @param  string  $pattern
	 * @param  string  $name
	 * @param  array|null  $methods
	 * @return void
	 */
	public function when($pattern, $name, $methods = null)
	{
		if ( ! is_null($methods)) $methods = array_map('strtoupper', (array) $methods);

		$this->patternFilters[$pattern][] = compact('name', 'methods');
	}

	/**
	 * Register a regular expression based filter with the router.
	 *
	 * @param  string     $pattern
	 * @param  string     $name
	 * @param  array|null $methods
	 * @return void
	 */
	public function whenRegex($pattern, $name, $methods = null)
	{
		if ( ! is_null($methods)) $methods = array_map('strtoupper', (array) $methods);

		$this->regexFilters[$pattern][] = compact('name', 'methods');
	}

	/**
	 * Register a model binder for a wildcard.
	 *
	 * @param  string  $key
	 * @param  string  $class
	 * @param  \Closure|null  $callback
	 * @return void
	 *
	 * @throws NotFoundHttpException
	 */
	public function model($key, $class, Closure $callback = null)
	{
		$this->bind($key, function($value) use ($class, $callback)
		{
			if (is_null($value)) return;

			// For model binders, we will attempt to retrieve the models using the first
			// method on the model instance. If we cannot retrieve the models we'll
			// throw a not found exception otherwise we will return the instance.
			if ($model = (new $class)->find($value))
			{
				return $model;
			}

			// If a callback was supplied to the method we will call that to determine
			// what we should do when the model is not found. This just gives these
			// developer a little greater flexibility to decide what will happen.
			if ($callback instanceof Closure)
			{
				return call_user_func($callback, $value);
			}

			throw new NotFoundHttpException;
		});
	}

	/**
	 * Add a new route parameter binder.
	 *
	 * @param  string  $key
	 * @param  string|callable  $binder
	 * @return void
	 */
	public function bind($key, $binder)
	{
		if (is_string($binder))
		{
			$binder = $this->createClassBinding($binder);
		}

		$this->binders[str_replace('-', '_', $key)] = $binder;
	}

	/**
	 * Create a class based binding using the IoC container.
	 *
	 * @param  string    $binding
	 * @return \Closure
	 */
	public function createClassBinding($binding)
	{
		return function($value, $route) use ($binding)
		{
			// If the binding has an @ sign, we will assume it's being used to delimit
			// the class name from the bind method name. This allows for bindings
			// to run multiple bind methods in a single class for convenience.
			$segments = explode('@', $binding);

			$method = count($segments) == 2 ? $segments[1] : 'bind';

			$callable = [$this->container->make($segments[0]), $method];

			return call_user_func($callable, $value, $route);
		};
	}

	/**
	 * Set a global where pattern on all routes
	 *
	 * @param  string  $key
	 * @param  string  $pattern
	 * @return void
	 */
	public function pattern($key, $pattern)
	{
		$this->patterns[$key] = $pattern;
	}

	/**
	 * Set a group of global where patterns on all routes
	 *
	 * @param  array  $patterns
	 * @return void
	 */
	public function patterns($patterns)
	{
		foreach ($patterns as $key => $pattern)
		{
			$this->pattern($key, $pattern);
		}
	}

	/**
	 * Call the given filter with the request and response.
	 *
	 * @param  string  $filter
	 * @param  \Illuminate\Http\Request   $request
	 * @param  \Illuminate\Http\Response  $response
	 * @return mixed
	 */
	protected function callFilter($filter, $request, $response = null)
	{
		return $this->events->until('router.'.$filter, array($request, $response));
	}

	/**
	 * Call the given route's before filters.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @param  \Illuminate\Http\Request  $request
	 * @return mixed
	 */
	public function callRouteBefore($route, $request)
	{
		$response = $this->callPatternFilters($route, $request);

		return $response ?: $this->callAttachedBefores($route, $request);
	}

	/**
	 * Call the pattern based filters for the request.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @param  \Illuminate\Http\Request  $request
	 * @return mixed|null
	 */
	protected function callPatternFilters($route, $request)
	{
		foreach ($this->findPatternFilters($request) as $filter => $parameters)
		{
			$response = $this->callRouteFilter($filter, $parameters, $route, $request);

			if ( ! is_null($response)) return $response;
		}
	}

	/**
	 * Find the patterned filters matching a request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function findPatternFilters($request)
	{
		$results = array();

		list($path, $method) = array($request->path(), $request->getMethod());

		foreach ($this->patternFilters as $pattern => $filters)
		{
			// To find the patterned middlewares for a request, we just need to check these
			// registered patterns against the path info for the current request to this
			// applications, and when it matches we will merge into these middlewares.
			if (str_is($pattern, $path))
			{
				$merge = $this->patternsByMethod($method, $filters);

				$results = array_merge($results, $merge);
			}
		}

		foreach ($this->regexFilters as $pattern => $filters)
		{
			// To find the patterned middlewares for a request, we just need to check these
			// registered patterns against the path info for the current request to this
			// applications, and when it matches we will merge into these middlewares.
			if (preg_match($pattern, $path))
			{
				$merge = $this->patternsByMethod($method, $filters);

				$results = array_merge($results, $merge);
			}
		}

		return $results;
	}

	/**
	 * Filter pattern filters that don't apply to the request verb.
	 *
	 * @param  string  $method
	 * @param  array   $filters
	 * @return array
	 */
	protected function patternsByMethod($method, $filters)
	{
		$results = array();

		foreach ($filters as $filter)
		{
			// The idea here is to check and see if the pattern filter applies to this HTTP
			// request based on the request methods. Pattern filters might be limited by
			// the request verb to make it simply to assign to the given verb at once.
			if ($this->filterSupportsMethod($filter, $method))
			{
				$parsed = Route::parseFilters($filter['name']);

				$results = array_merge($results, $parsed);
			}
		}

		return $results;
	}

	/**
	 * Determine if the given pattern filters applies to a given method.
	 *
	 * @param  array  $filter
	 * @param  array  $method
	 * @return bool
	 */
	protected function filterSupportsMethod($filter, $method)
	{
		$methods = $filter['methods'];

		return is_null($methods) || in_array($method, $methods);
	}

	/**
	 * Call the given route's before (non-pattern) filters.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @param  \Illuminate\Http\Request  $request
	 * @return mixed
	 */
	protected function callAttachedBefores($route, $request)
	{
		foreach ($route->beforeFilters() as $filter => $parameters)
		{
			$response = $this->callRouteFilter($filter, $parameters, $route, $request);

			if ( ! is_null($response)) return $response;
		}
	}

	/**
	 * Call the given route's after filters.
	 *
	 * @param  \Illuminate\Routing\Route  $route
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Illuminate\Http\Response  $response
	 * @return mixed
	 */
	public function callRouteAfter($route, $request, $response)
	{
		foreach ($route->afterFilters() as $filter => $parameters)
		{
			$this->callRouteFilter($filter, $parameters, $route, $request, $response);
		}
	}

	/**
	 * Call the given route filter.
	 *
	 * @param  string  $filter
	 * @param  array  $parameters
	 * @param  \Illuminate\Routing\Route  $route
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Illuminate\Http\Response|null $response
	 * @return mixed
	 */
	public function callRouteFilter($filter, $parameters, $route, $request, $response = null)
	{
		$data = array_merge(array($route, $request, $response), $parameters);

		return $this->events->until('router.filter: '.$filter, $this->cleanFilterParameters($data));
	}

	/**
	 * Clean the parameters being passed to a filter callback.
	 *
	 * @param  array  $parameters
	 * @return array
	 */
	protected function cleanFilterParameters(array $parameters)
	{
		return array_filter($parameters, function($p)
		{
			return ! is_null($p) && $p !== '';
		});
	}

	/**
	 * Create a response instance from the given value.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @param  mixed  $response
	 * @return \Illuminate\Http\Response
	 */
	protected function prepareResponse($request, $response)
	{
		if ( ! $response instanceof SymfonyResponse)
		{
			$response = new Response($response);//实例化SymfonyResponse类对象，$response=响应内容
		}
		return $response->prepare($request);//设置相关响应头，返回响应对象
	}

	/**
	 * Determine if the router currently has a group stack.
	 *
	 * @return bool
	 */
	public function hasGroupStack()
	{
		return ! empty($this->groupStack);
	}

	/**
	 * Get the current group stack for the router.
	 *
	 * @return array
	 */
	public function getGroupStack()
	{
		return $this->groupStack;
	}

	/**
	 * Get a route parameter for the current route.
	 *
	 * @param  string  $key
	 * @param  string  $default
	 * @return mixed
	 */
	public function input($key, $default = null)
	{
		return $this->current()->parameter($key, $default);
	}

	/**
	 * Get the currently dispatched route instance.
	 *
	 * @return \Illuminate\Routing\Route
	 */
	public function getCurrentRoute()
	{
		return $this->current();
	}

	/**
	 * Get the currently dispatched route instance.
	 *
	 * @return \Illuminate\Routing\Route
	 */
	public function current()
	{
		return $this->current;
	}

	/**
	 * Check if a route with the given name exists.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function has($name)
	{
		return $this->routes->hasNamedRoute($name);
	}

	/**
	 * Get the current route name.
	 *
	 * @return string|null
	 */
	public function currentRouteName()
	{
		return $this->current() ? $this->current()->getName() : null;
	}

	/**
	 * Alias for the "currentRouteNamed" method.
	 *
	 * @param  mixed  string
	 * @return bool
	 */
	public function is()
	{
		foreach (func_get_args() as $pattern)
		{
			if (str_is($pattern, $this->currentRouteName()))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the current route matches a given name.
	 *
	 * @param  string  $name
	 * @return bool
	 */
	public function currentRouteNamed($name)
	{
		return $this->current() ? $this->current()->getName() == $name : false;
	}

	/**
	 * Get the current route action.
	 *
	 * @return string|null
	 */
	public function currentRouteAction()
	{
		if ( ! $this->current()) return;  //没有匹配到路由对象

		$action = $this->current()->getAction();//获取匹配到的route路由对象的action属性值

		return isset($action['controller']) ? $action['controller'] : null;
	}

	/**
	 * Alias for the "currentRouteUses" method.
	 *
	 * @param  mixed  string
	 * @return bool
	 */
	public function uses()
	{
		foreach (func_get_args() as $pattern)
		{
			if (str_is($pattern, $this->currentRouteAction()))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the current route action matches a given action.
	 *
	 * @param  string  $action
	 * @return bool
	 */
	public function currentRouteUses($action)
	{
		return $this->currentRouteAction() == $action;
	}

	/**
	 * Get the request currently being dispatched.
	 *
	 * @return \Illuminate\Http\Request
	 */
	public function getCurrentRequest()
	{
		return $this->currentRequest;
	}

	/**
	 * Get the underlying route collection.
	 *
	 * @return \Illuminate\Routing\RouteCollection
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Set the route collection instance.
	 *
	 * @param  \Illuminate\Routing\RouteCollection  $routes
	 * @return void
	 */
	public function setRoutes(RouteCollection $routes)
	{
		foreach ($routes as $route)
		{
			$route->setContainer($this->container);
		}

		$this->routes = $routes;

		$this->container->instance('routes', $this->routes);
	}

	/**
	 * Get the global "where" patterns.
	 *
	 * @return array
	 */
	public function getPatterns()
	{
		return $this->patterns;
	}

}
