<?php namespace Illuminate\Container;

use Closure;
use ArrayAccess;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use InvalidArgumentException;
use Illuminate\Contracts\Container\Container as ContainerContract;

class Container implements ArrayAccess, ContainerContract {

	/**
	 * The current globally available container (if any).
	 * app对象,容器对象
	 * @var static
	 */
	protected static $instance;

	/**
	 * An array of the types that have been resolved.
	 *
	 * @var array
	 */
	protected $resolved = [];

	/**
	 * The container's bindings.
	 *
	 * @var array
	 */
	protected $bindings = [];

	/**
	 * The container's shared instances.
	 * 所有类对象,['字符串'=>对象, ]
	 *
	 * @var array=[
	'app'=>app对象,
			'Illuminate\Container\Container'=>app对象,
	 		'app'=>app对象,
			'path'=>项目根目录/app，
			'path.base'=>项目根目录，
			'path.config'=>项目根目录/config，
			'path.database'=>项目根目录/database，
			'path.lang'=>项目根目录/resources/lang，
			'path.public'=>项目根目录/public，
			'path.storage'=>项目根目录/storage，
			'request'=>$request对象
			];
	 */
	protected $instances = [];

	/**
	 * 存放字符串=>别名
	 * @var array=[字符串=>别名, 'Illuminate\\Foundation\\Application'=>'app']
	 */
	protected $aliases = [];

	/**
	 * The extension closures for services.
	 * 扩展闭包服务, ['$abstract'=>"方法(abstract对象, app对象)", ]
	 * @var array
	 */
	protected $extenders = [];

	/**
	 * All of the registered tags.
	 *
	 * @var array
	 */
	protected $tags = [];

	/**
	 * The stack of concretions being current built.
	 *
	 * @var array
	 */
	protected $buildStack = [];

	/**
	 * The contextual binding map.
	 *
	 * @var array
	 */
	public $contextual = [];

	/**
	 * All of the registered rebound callbacks.
	 *
	 * @var array
	 */
	protected $reboundCallbacks = [];

	/**
	 * All of the global resolving callbacks.
	 *
	 * @var array
	 */
	protected $globalResolvingCallbacks = [];

	/**
	 * All of the global after resolving callbacks.
	 *
	 * @var array
	 */
	protected $globalAfterResolvingCallbacks = [];

	/**
	 * All of the after resolving callbacks by class type.
	 *
	 * @var array
	 */
	protected $resolvingCallbacks = [];

	/**
	 * All of the after resolving callbacks by class type.
	 *
	 * @var array
	 */
	protected $afterResolvingCallbacks = [];

	/**
	 * Define a contextual binding.
	 *
	 * @param  string  $concrete
	 * @return \Illuminate\Contracts\Container\ContextualBindingBuilder
	 */
	public function when($concrete)
	{
		return new ContextualBindingBuilder($this, $concrete);
	}

	/**
	 * Determine if a given string is resolvable.
	 *
	 * @param  string  $abstract
	 * @return bool
	 */
	protected function resolvable($abstract)
	{
		return $this->bound($abstract);
	}

	/**
	 * Determine if the given abstract type has been bound.
	 * 捆绑，实例，别名
	 * @param  string  $abstract
	 * @return bool
	 */
	public function bound($abstract)
	{
		return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]) || $this->isAlias($abstract);
	}

	/**
	 * Determine if the given abstract type has been resolved.
	 * 是否是instances属性key或resolved属性key
	 * @param  string  $abstract
	 * @return bool
	 */
	public function resolved($abstract)
	{
		return isset($this->resolved[$abstract]) || isset($this->instances[$abstract]);
	}

	/**
	 * Determine if a given string is an alias.
	 * $name是否有别名
	 * @param  string  $name
	 * @return bool
	 */
	public function isAlias($name)
	{
		return isset($this->aliases[$name]);
	}

	/**
	 * Register a binding with the container.
	 *
	 * @param  string|array  $abstract = 字符串 或者 数组[$abstract=> $alias]  抽象
	 * @param  \Closure|string|null  $concrete=闭包 或 字符串 或 null   具体物
	 * @param  bool  $shared
	 * @return void
	 */
	public function bind($abstract, $concrete = null, $shared = false)
	{
		//
		if (is_array($abstract))
		{   #array($abstract=> $alias)
			list($abstract, $alias) = $this->extractAlias($abstract);
			//设置别名属性
			$this->alias($abstract, $alias);//$this->aliases[$alias] = $abstract;
		}

		#删除instances[$abstract]和aliases[$abstract]属性的key
		$this->dropStaleInstances($abstract);

		if (is_null($concrete))
		{
			$concrete = $abstract;//build($concrete, $param)
		}

		if ( ! $concrete instanceof Closure)
		{#不是闭包
			//返回闭包,闭包接收参数(对象c，参数1)即调用对象c的make($concrete,参数1)或者build方法（$concrete,参数1）
			$concrete = $this->getClosure($abstract, $concrete);//返回闭包
		}
		#app对象->bind('events', function($app){闭包}, true);
		#	=>则是设置$this->bindings['events']=['concrete'=>function(对象){}, 'shared'=>true ]
		#app对象->bind('abc', 'xyz', true);
		#  =>则是设置$this->bindings['abc']=['concrete'=>function(对象1,参数1){对象1->make(xyz,参数1)}, 'shared'=>true]
		$this->bindings[$abstract] = compact('concrete', 'shared');

		//是否是instances属性key或resolved属性key
		if ($this->resolved($abstract))
		{//是
			$this->rebound($abstract);#调用一次make方法 $instance = $this->make($abstract);
		}
	}

	/**
	 * Get the Closure to be used when building a type.
	 * $abstract==$concrete相等build,不相等make
	 * @param  string  $abstract 抽象
	 * @param  string  $concrete 具体物
	 * @return \Closure 返回闭包，闭包接收参数(对象c，参数1)即调用对象c的make($concrete,参数1)或者build方法（$concrete,参数1）
	 */
	protected function getClosure($abstract, $concrete)
	{
		return function($c, $parameters = []) use ($abstract, $concrete)
		{
			$method = ($abstract == $concrete) ? 'build' : 'make';

			return $c->$method($concrete, $parameters);
		};
	}

	/**
	 * Add a contextual binding to the container.
	 * 添加上下文关系
	 * @param  string  $concrete 具体物
	 * @param  string  $abstract 抽象
	 * @param  \Closure|string  $implementation  字符串，闭包
	 */
	public function addContextualBinding($concrete, $abstract, $implementation)
	{
		$this->contextual[$concrete][$abstract] = $implementation;
	}

	/**
	 * Register a binding if it hasn't already been registered.
	 * 不在限制范围内则实例化
	 * @param  string  $abstract
	 * @param  \Closure|string|null  $concrete
	 * @param  bool  $shared
	 * @return void
	 */
	public function bindIf($abstract, $concrete = null, $shared = false)
	{
		if ( ! $this->bound($abstract))
		{
			$this->bind($abstract, $concrete, $shared);
		}
	}

	/**
	 * Register a shared binding in the container.
	 *
	 * @param  string  $abstract
	 * @param  \Closure|string|null  $concrete
	 * @return void
	 */
	public function singleton($abstract, $concrete = null)
	{
		$this->bind($abstract, $concrete, true);
	}

	/**
	 * 返回闭包,闭包里面的代码执行之后,如果不是返回null则不再执行且只返回上一次返回的内容
	 * @param  \Closure  $closure
	 * @return \Closure
	 */
	public function share(Closure $closure)
	{
		return function($container) use ($closure)
		{
			static $object;
			if (is_null($object))
			{
				$object = $closure($container);
			}
			return $object;
		};
	}

	/**
	 * Bind a shared Closure into the container.
	 *
	 * @param  string    $abstract 抽象
	 * @param  \Closure  $closure
	 * @return void
	 */
	public function bindShared($abstract, Closure $closure)
	{
		$this->bind($abstract, $this->share($closure), true);
	}

	/**
	 * "Extend" an abstract type in the container.
	 *
	 * @param  string    $abstract 抽象
	 * @param  \Closure  $closure 闭包接收参数(abstract对象,app对象)
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function extend($abstract, Closure $closure)
	{
		if (isset($this->instances[$abstract]))
		{
			$this->instances[$abstract] = $closure($this->instances[$abstract], $this);

			$this->rebound($abstract);
		}
		else
		{
			$this->extenders[$abstract][] = $closure;
		}
	}

	/**
	 * Register an existing instance as shared in the container.
	 * $this->instance('app', $this);
	 * $this->instance('Illuminate\Container\Container', $this);
	 * $this->instance('path', $this->path());
	 * @param  string  $abstract 字符串|数组array('abstract'=>'别名')
	 * @param  mixed   $instance =对象|字符串
	 * @return void
	 */
	public function instance($abstract, $instance)
	{
		if (is_array($abstract))
		{	//分析$abstract数组,返回['abstract','别名'];
			list($abstract, $alias) = $this->extractAlias($abstract);
			$this->alias($abstract, $alias);//设置属性$this->aliases[$alias] = $abstract;
		}
		//解决 不能互为别名
		unset($this->aliases[$abstract]);

		//是否捆绑，实例，别名， 如果$abstract一样重复调用则一定是返回true
		$bound = $this->bound($abstract);//$abstrace是本类的bindings，instances，aliases三个属性之一的key
        //设置instances属性key=》值
		$this->instances[$abstract] = $instance;

		if ($bound)
		{//是上面的三个属性key之一 则重新捆绑
			$this->rebound($abstract);
		}
	}

	/**
	 * Assign a set of tags to a given binding.
	 *
	 * @param  array|string  $abstracts
	 * @param  array|mixed   ...$tags
	 * @return void
	 */
	public function tag($abstracts, $tags)
	{
		$tags = is_array($tags) ? $tags : array_slice(func_get_args(), 1);

		foreach ($tags as $tag)
		{
			if ( ! isset($this->tags[$tag])) $this->tags[$tag] = [];

			foreach ((array) $abstracts as $abstract)
			{
				$this->tags[$tag][] = $abstract;
			}
		}
	}

	/**
	 * Resolve all of the bindings for a given tag.
	 *
	 * @param  string  $tag
	 * @return array
	 */
	public function tagged($tag)
	{
		$results = [];

		foreach ($this->tags[$tag] as $abstract)
		{
			$results[] = $this->make($abstract);
		}

		return $results;
	}

	/**
	 * Alias a type to a different name.
	 *
	 * @param  string  $abstract
	 * @param  string  $alias
	 * @return void
	 */
	public function alias($abstract, $alias)
	{
		$this->aliases[$alias] = $abstract;
	}

	/**
	 * Extract the type and alias from a given definition.
	 *
	 * @param  array  $definition
	 * @return array
	 */
	protected function extractAlias(array $definition)
	{
		return [key($definition), current($definition)];
	}

	/**
	 * Bind a new callback to an abstract's rebind event.
	 *
	 * @param  string    $abstract
	 * @param  \Closure  $callback = 闭包($app对象, $abstract的对象)
	 * @return mixed
	 */
	public function rebinding($abstract, Closure $callback)
	{
		$this->reboundCallbacks[$abstract][] = $callback;

		if ($this->bound($abstract)) return $this->make($abstract);
	}

	/**
	 * Refresh an instance on the given target and method.
	 *
	 * @param  string  $abstract
	 * @param  mixed   $target
	 * @param  string  $method
	 * @return mixed
	 */
	public function refresh($abstract, $target, $method)
	{
		return $this->rebinding($abstract, function($app, $instance) use ($target, $method)
		{
			$target->{$method}($instance);
		});
	}

	/**
	 * Fire the "rebound" callbacks for the given abstract type.
	 *
	 * @param  string  $abstract
	 * @return void
	 */
	protected function rebound($abstract)
	{
		$instance = $this->make($abstract);//获取对象

		foreach ($this->getReboundCallbacks($abstract) as $callback)
		{
			call_user_func($callback, $this, $instance);
		}
	}

	/**
	 * Get the rebound callbacks for a given type.
	 *
	 * @param  string  $abstract
	 * @return array
	 */
	protected function getReboundCallbacks($abstract)
	{
		if (isset($this->reboundCallbacks[$abstract]))
		{//存在值,值是可以被call_user_func函数调用的,并接收2个参数,分别是app对象和$abstract对应的类对象
			return $this->reboundCallbacks[$abstract];
		}

		return [];
	}

	/**
	 * Wrap the given closure such that its dependencies will be injected when executed.
	 *
	 * @param  \Closure  $callback
	 * @param  array  $parameters
	 * @return \Closure
	 */
	public function wrap(Closure $callback, array $parameters = [])
	{
		return function() use ($callback, $parameters)
		{
			return $this->call($callback, $parameters);
		};
	}

	/**
	 * Call the given Closure / class@method and inject its dependencies.
	 * $this->call([$provider对象, 'boot']);
	 * @param  callable|string  $callback=方法名 或者 类名@方法名 或者 [对象, '方法名'] 或 ['类名', '方法名']
	 * @param  array  $parameters
	 * @param  string|null  $defaultMethod
	 * @return mixed
	 */
	public function call($callback, array $parameters = [], $defaultMethod = null)
	{
		//字符串且存在@符号 或 $defaultMethod值为真
		if ($this->isCallableWithAtSign($callback) || $defaultMethod)
		{//$callback=字符串存在@符号或者$defaultMethod有值
			return $this->callClass($callback, $parameters, $defaultMethod);
		}
		//返回$callback函数要接收的参数
		$dependencies = $this->getMethodDependencies($callback, $parameters);
		return call_user_func_array($callback, $dependencies);
	}

	/**
	 * 是否字符串且字符串中包含@符号
	 * @param  mixed  $callback
	 * @return bool
	 */
	protected function isCallableWithAtSign($callback)
	{
		if ( ! is_string($callback)) return false;
		return strpos($callback, '@') !== false;
	}

	/**
	 * Get all dependencies for a given method.
	 * 返回$callback函数要接收的参数
	 * @param  callable|string  $callback
	 * @param  array  $parameters
	 * @return array
	 */
	protected function getMethodDependencies($callback, $parameters = [])
	{
		$dependencies = [];

		foreach ($this->getCallReflector($callback)->getParameters() as $key => $parameter)
		{
			$this->addDependencyForCallParameter($parameter, $parameters, $dependencies);
		}

		return array_merge($dependencies, $parameters);
	}

	/**
	 * Get the proper reflection instance for the given callback.
	 *
	 * @param  callable|string  $callback
	 * @return \ReflectionFunctionAbstract
	 */
	protected function getCallReflector($callback)
	{
		if (is_string($callback) && strpos($callback, '::') !== false)
		{//abc::hello
			$callback = explode('::', $callback);
		}

		if (is_array($callback))
		{//反射类中方法
			return new ReflectionMethod($callback[0], $callback[1]);
		}
		//反射函数
		return new ReflectionFunction($callback);
	}

	/**
	 * Get the dependency for the given call parameter.
	 *
	 * @param  \ReflectionParameter  $parameter 反射出的参数对象
	 * @param  array  $parameters 要传递给参数的值,可选
	 * @param  array  $dependencies 函数参数要接收的值
	 * @return mixed
	 */
	protected function addDependencyForCallParameter(ReflectionParameter $parameter, array &$parameters, &$dependencies)
	{
		if (array_key_exists($parameter->name, $parameters))
		{//$parameter->name 参数名在$parameters数组内
			$dependencies[] = $parameters[$parameter->name];
			unset($parameters[$parameter->name]);
		}
		elseif ($parameter->getClass())
		{//是反射类， 则可以$parameter->getClass()->name获取类名
			$dependencies[] = $this->make($parameter->getClass()->name);
		}
		elseif ($parameter->isDefaultValueAvailable())
		{//参数存在默认值
			$dependencies[] = $parameter->getDefaultValue();//返回参数默认值
		}
	}

	/**
	 * Call a string reference to a class using Class@method syntax.
	 *
	 * @param  string  $target=类名@方法名
	 * @param  array  $parameters
	 * @param  string|null  $defaultMethod
	 * @return mixed
	 */
	protected function callClass($target, array $parameters = [], $defaultMethod = null)
	{
		$segments = explode('@', $target);
		$method = count($segments) == 2 ? $segments[1] : $defaultMethod;
		if (is_null($method))
		{
			throw new InvalidArgumentException("Method not provided.");
		}
		return $this->call([$this->make($segments[0]), $method], $parameters);
	}

	/**
	 * Resolve the given type from the container.
	 * 获取类对象,实例化类对象
	 * @param  string  $abstract 字符串
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function make($abstract, $parameters = [])
	{
		//如果$abstract是别名则返回真实的$abstract
		$abstract = $this->getAlias($abstract);// $this->aliases[$abstract] || $abstract
		//其实就是启动单例作用
		if (isset($this->instances[$abstract]))
		{	#存在instances属性key
			return $this->instances[$abstract];
		}
		$concrete = $this->getConcrete($abstract);//从bindings属性中取闭包，不是bindings属性则带\原样返回

		//return $concrete === $abstract || $concrete instanceof Closure;
		if ($this->isBuildable($concrete, $abstract))
		{#是闭包或者$concrete == $abstract， 
			$object = $this->build($concrete, $parameters);//返回对象，如果$concrete是闭包则接收app对象和$parameters参数并返回对象，如果$concrete=字符串则是反射出类对象
		}else {//递归实例化
			$object = $this->make($concrete, $parameters);
		}

		//
		foreach ($this->getExtenders($abstract) as $extender)
		{//遍历app对象的extenders[$abstract]属性值
			$object = $extender($object, $this);
		}

		//单例
		if ($this->isShared($abstract)) {#是instances[$abstract]属性值 或者 bindings[$abstract]['shared']=true
			$this->instances[$abstract] = $object;
		}

		$this->fireResolvingCallbacks($abstract, $object);
		//标记make过
		$this->resolved[$abstract] = true;

		return $object;
	}

	/**
	 * Get the concrete type for a given abstract.
	 *
	 * @param  string  $abstract
	 * @return mixed   $concrete 具体的
	 */
	protected function getConcrete($abstract)
	{
		if ( ! is_null($concrete = $this->getContextualConcrete($abstract)))
		{
			return $concrete;
		}
		if ( ! isset($this->bindings[$abstract]))
		{	//判断是否不以\开头的字符串 且 \$abstract已经存在则返回绝对的串否则原样返回$abstract
			if ($this->missingLeadingSlash($abstract) &&
				isset($this->bindings['\\'.$abstract]))
			{//字符串且不以\开头，如abc\xyz  且\abc\xyz是bindings的key则加前缀\
				$abstract = '\\'.$abstract;
			}
			//原样字符串返回或者加了\的
			return $abstract;
		}

		return $this->bindings[$abstract]['concrete'];
	}

	/**
	 * Get the contextual concrete binding for the given abstract.
	 *
	 * @param  string  $abstract
	 * @return string
	 */
	protected function getContextualConcrete($abstract)
	{
		if (isset($this->contextual[end($this->buildStack)][$abstract]))
		{
			return $this->contextual[end($this->buildStack)][$abstract];
		}
	}

	/**
	 * Determine if the given abstract has a leading slash.
	 *  是字符串且不以\开头  如abc， abc\xyz
	 * 判断是否不以\开头的字符串
	 * @param  string  $abstract
	 * @return bool
	 */
	protected function missingLeadingSlash($abstract)
	{
		return is_string($abstract) && strpos($abstract, '\\') !== 0;
	}

	/**
	 * Get the extender callbacks for a given type.
	 *
	 * @param  string  $abstract
	 * @return array
	 */
	protected function getExtenders($abstract)
	{
		if (isset($this->extenders[$abstract]))
		{
			return $this->extenders[$abstract];
		}

		return [];
	}

	/**
	 * Instantiate a concrete instance of the given type.
	 * 实例化对象
	 * @param  string  $concrete 字符串类名或闭包
	 * @param  array   $parameters 传给闭包或类构造函数的参数= [0=>'第1个参数值', '1'=>'第2个参数值', '参数名'=>'参数名对应的值']
	 * @return mixed
	 *
	 * @throws BindingResolutionException
	 */
	public function build($concrete, $parameters = [])
	{
		if ($concrete instanceof Closure)
		{#是闭包则闭包(app对象, 参数)
			return $concrete($this, $parameters);
		}
		//是字符串 则反射类
		$reflector = new ReflectionClass($concrete);

		if ( ! $reflector->isInstantiable()) {#类不能被可实例化
			$message = "Target [$concrete] is not instantiable.";
			throw new BindingResolutionException($message);
		}
		//把类名存入反射堆数组中
		$this->buildStack[] = $concrete;
		//获取类的构造方法 ，返回ReflectionMethod 对象
		$constructor = $reflector->getConstructor();

		if (is_null($constructor)) {#不存在构造方法
			array_pop($this->buildStack);//将数组最后一个单元弹出（出栈）
			//直接实例化例
			return new $concrete;
		}

		$dependencies = $constructor->getParameters();
		//如果$parameters的key是数字下标则换成参数名做下标
		$parameters = $this->keyParametersByArgument( $dependencies, $parameters );

		$instances = $this->getDependencies( $dependencies, $parameters );
		//从堆中移除
		array_pop($this->buildStack);
		//返回类对象，并把$instances参数给构造方法,执行构造函数
		return $reflector->newInstanceArgs($instances);
	}

	/**
	 * Resolve all of the dependencies from the ReflectionParameters.
	 *
	 * @param  array  $parameters 反射出来的参数对象
	 * @param  array  $primitives = [参数名=>值, 参数名n=>值n]
	 * @return array
	 */
	protected function getDependencies($parameters, array $primitives = [])
	{
		$dependencies = [];

		foreach ($parameters as $parameter)
		{
			$dependency = $parameter->getClass();

			if (array_key_exists($parameter->name, $primitives))
			{//存在参数值
				$dependencies[] = $primitives[$parameter->name];
			}
			elseif (is_null($dependency))
			{//不是类对象,则获取$parameter参数默认值
				$dependencies[] = $this->resolveNonClass($parameter);
			}
			else
			{//参数接收的是类对象
				$dependencies[] = $this->resolveClass($parameter);
			}
		}

		return (array) $dependencies;
	}

	/**
	 * Resolve a non-class hinted dependency.
	 *
	 * @param  \ReflectionParameter  $parameter
	 * @return mixed
	 *
	 * @throws BindingResolutionException
	 */
	protected function resolveNonClass(ReflectionParameter $parameter)
	{
		if ($parameter->isDefaultValueAvailable())
		{//获取默认值
			return $parameter->getDefaultValue();
		}

		$message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";

		throw new BindingResolutionException($message);
	}

	/**
	 * Resolve a class based dependency from the container.
	 *
	 * @param  \ReflectionParameter  $parameter 反射出来的参数
	 * @return mixed
	 *
	 * @throws BindingResolutionException
	 */
	protected function resolveClass(ReflectionParameter $parameter)
	{
		try
		{
			return $this->make($parameter->getClass()->name);//参数类名进行实例化
		}
		catch (BindingResolutionException $e)
		{
			if ($parameter->isOptional())
			{//获取默认参数值
				return $parameter->getDefaultValue();
			}

			throw $e;
		}
	}

	/**
	 * If extra parameters are passed by numeric ID, rekey them by argument name.
	 * 如果$parameters的key是数字下标则换成参数名做下标
	 * @param  array  $dependencies 反射出来的参数对象
	 * @param  array  $parameters = [0=>值, 1=>值n]
	 * @return array
	 */
	protected function keyParametersByArgument(array $dependencies, array $parameters)
	{
		foreach ($parameters as $key => $value)
		{
			if (is_numeric($key))
			{
				unset($parameters[$key]);

				$parameters[$dependencies[$key]->name] = $value;
			}
		}

		return $parameters;
	}

	/**
	 * Register a new resolving callback.
	 *
	 * @param  string    $abstract
	 * @param  \Closure  $callback
	 * @return void
	 */
	public function resolving($abstract, Closure $callback = null)
	{
		if ($callback === null && $abstract instanceof Closure)
		{
			$this->resolvingCallback($abstract);
		}
		else
		{
			$this->resolvingCallbacks[$abstract][] = $callback;
		}
	}

	/**
	 * Register a new after resolving callback for all types.
	 *
	 * @param  string   $abstract
	 * @param  \Closure $callback
	 * @return void
	 */
	public function afterResolving($abstract, Closure $callback = null)
	{
		if ($abstract instanceof Closure && $callback === null)
		{
			$this->afterResolvingCallback($abstract);
		}
		else
		{
			$this->afterResolvingCallbacks[$abstract][] = $callback;
		}
	}

	/**
	 * Register a new resolving callback by type of its first argument.
	 *
	 * @param  \Closure  $callback
	 * @return void
	 */
	protected function resolvingCallback(Closure $callback)
	{
		$abstract = $this->getFunctionHint($callback);

		if ($abstract)
		{
			$this->resolvingCallbacks[$abstract][] = $callback;
		}
		else
		{
			$this->globalResolvingCallbacks[] = $callback;
		}
	}

	/**
	 * Register a new after resolving callback by type of its first argument.
	 *
	 * @param  \Closure  $callback
	 * @return void
	 */
	protected function afterResolvingCallback(Closure $callback)
	{
		$abstract = $this->getFunctionHint($callback);

		if ($abstract)
		{
			$this->afterResolvingCallbacks[$abstract][] = $callback;
		}
		else
		{
			$this->globalAfterResolvingCallbacks[] = $callback;
		}
	}

	/**
	 * Get the type hint for this closure's first argument.
	 *
	 * @param  \Closure  $callback
	 * @return mixed
	 */
	protected function getFunctionHint(Closure $callback)
	{
		$function = new ReflectionFunction($callback);

		if ($function->getNumberOfParameters() == 0)
		{
			return null;
		}

		$expected = $function->getParameters()[0];

		if ( ! $expected->getClass())
		{
			return null;
		}

		return $expected->getClass()->name;
	}

	/**
	 * Fire all of the resolving callbacks.
	 *
	 * @param  string  $abstract=字符串
	 * @param  mixed   $object=对象
	 * @return void
	 */
	protected function fireResolvingCallbacks($abstract, $object)
	{
		$this->fireCallbackArray($object, $this->globalResolvingCallbacks);

		$this->fireCallbackArray(
			$object, $this->getCallbacksForType(
				$abstract, $object, $this->resolvingCallbacks
			)
		);

		$this->fireCallbackArray($object, $this->globalAfterResolvingCallbacks);

		$this->fireCallbackArray(
			$object, $this->getCallbacksForType(
				$abstract, $object, $this->afterResolvingCallbacks
			)
		);
	}

	/**
	 * Get all callbacks for a given type.
	 * 把$abstract等于type或 $object上type对象的值做合并
	 * @param  string  $abstract=字符串
	 * @param  object  $object=对象
	 * @param  array   $callbacksPerType=数组=array('type'=>array(数组值n),'typen'=>array(数组值n), )
	 *
	 * @return array
	 */
	protected function getCallbacksForType($abstract, $object, array $callbacksPerType)
	{
		$results = [];

		foreach ($callbacksPerType as $type => $callbacks)
		{
			if ($type === $abstract || $object instanceof $type)
			{
				$results = array_merge($results, $callbacks);
			}
		}

		return $results;
	}

	/**
	 * Fire an array of callbacks with an object.
	 * $callbacks数组中每个单元都执行,且函数接收$object,$app对象
	 * @param  mixed  $object
	 * @param  array  $callbacks=array('函数名', '函数名2') 函数名接收参数($object, $app对象)
	 */
	protected function fireCallbackArray($object, array $callbacks)
	{
		foreach ($callbacks as $callback)
		{
			$callback($object, $this);
		}
	}

	/**
	 * Determine if a given type is shared.
	 *
	 * @param  string  $abstract
	 * @return bool
	 */
	public function isShared($abstract)
	{
		if (isset($this->bindings[$abstract]['shared']))
		{
			$shared = $this->bindings[$abstract]['shared'];
		}
		else
		{
			$shared = false;
		}

		return isset($this->instances[$abstract]) || $shared === true;
	}

	/**
	 * Determine if the given concrete is buildable.
	 * 是闭包或2个参数完全相等
	 * @param  mixed   $concrete 具体物
	 * @param  string  $abstract 抽象
	 * @return bool
	 */
	protected function isBuildable($concrete, $abstract)
	{
		return $concrete === $abstract || $concrete instanceof Closure;
	}

	/**
	 * Get the alias for an abstract if available.
	 *
	 * @param  string  $abstract
	 * @return string
	 */
	protected function getAlias($abstract)
	{
		return isset($this->aliases[$abstract]) ? $this->aliases[$abstract] : $abstract;
	}

	/**
	 * Get the container's bindings.
	 *
	 * @return array
	 */
	public function getBindings()
	{
		return $this->bindings;
	}

	/**
	 * Drop all of the stale instances and aliases.
	 *
	 * @param  string  $abstract
	 * @return void
	 */
	protected function dropStaleInstances($abstract)
	{
		unset($this->instances[$abstract], $this->aliases[$abstract]);
	}

	/**
	 * Remove a resolved instance from the instance cache.
	 *
	 * @param  string  $abstract
	 * @return void
	 */
	public function forgetInstance($abstract)
	{
		unset($this->instances[$abstract]);
	}

	/**
	 * Clear all of the instances from the container.
	 *
	 * @return void
	 */
	public function forgetInstances()
	{
		$this->instances = [];
	}

	/**
	 * Flush the container of all bindings and resolved instances.
	 *
	 * @return void
	 */
	public function flush()
	{
		$this->aliases = [];
		$this->resolved = [];
		$this->bindings = [];
		$this->instances = [];
	}

	/**
	 * Set the globally available instance of the container.
	 *
	 * @return static
	 */
	public static function getInstance()
	{
		return static::$instance;
	}

	/**
	 * Set the shared instance of the container.
	 *
	 * @param  \Illuminate\Contracts\Container\Container  $container
	 * @return void
	 */
	public static function setInstance(ContainerContract $container)
	{
		static::$instance = $container;
	}

	/**
	 * Determine if a given offset exists.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return isset($this->bindings[$key]);
	}

	/**
	 * Get the value at a given offset.
	 * 获取类对象,实例化类对象
	 * @param  string  $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->make($key);
	}

	/**
	 * Set the value at a given offset.
	 *
	 * @param  string  $key 字符串
	 * @param  mixed   $value 值或闭包
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		//值不是闭包,转成闭包
		if ( ! $value instanceof Closure)
		{
			$value = function() use ($value)
			{
				return $value;
			};
		}

		$this->bind($key, $value);
	}

	/**
	 * Unset the value at a given offset.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->bindings[$key], $this->instances[$key], $this->resolved[$key]);
	}

	/**
	 * Dynamically access container services.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this[$key];
	}

	/**
	 * Dynamically set container services.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this[$key] = $value;
	}

}
