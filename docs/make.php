<?php
$app = new Illuminate\Foundation\Application; #app对象
#$this是app对象['events']->xxx() 则的调用make方法返回对象->xxx()
$app->make($abstract, $parameters = array());   #$this->app->singleton('events', function($app)() {} 和make方法是成对的，一个注册，一个调用
{#
	$abstract = $this->getAlias($abstract); //从aliases属性key中有对应的串则用对应的串否则保持不变

	if (isset($this->deferredServices[$abstract]))
	{
		$this->loadDeferredProvider($abstract);
	}

	return parent::make($abstract, $parameters);
}
#父类的make
function make($abstract, $parameters = [])
{
	$abstract = $this->getAlias($abstract);//从aliases属性key中有对应的串则用对应的串否则保持不变

	if (isset($this->instances[$abstract]))
	{#存在instances属性key   用于实现单例
		return $this->instances[$abstract];
	}

	$concrete = $this->getConcrete($abstract);

	// 
	if ($this->isBuildable($concrete, $abstract))
	{#是闭包或者$concrete ==$abstract
		$object = $this->build($concrete, $parameters);#把当前app对象传给闭包, $concrete=闭包  ===>这是重点
	}
	else
	{//上线文， 递归调一次
		$object = $this->make($concrete, $parameters);
	}

	// If we defined any extenders for this type, we'll need to spin through them
	// and apply them to the object being built. This allows for the extension
	// of services, such as changing configuration or decorating the object.
	foreach ($this->getExtenders($abstract) as $extender)
	{#遍历extenders[$abstract]属性
		$object = $extender($object, $this);
	}

	//
	if ($this->isShared($abstract))
	{
		$this->instances[$abstract] = $object;
	}

	$this->fireResolvingCallbacks($abstract, $object);

	$this->resolved[$abstract] = true;

	return $object;
}

	protected function getConcrete($abstract)
	{
		if ( ! is_null($concrete = $this->getContextualConcrete($abstract)))
		{//上线文
			return $concrete;
		}

		// 没有binding过
		if ( ! isset($this->bindings[$abstract]))
		{
			if ($this->missingLeadingSlash($abstract) && isset($this->bindings['\\'.$abstract])) {//字符串且不以\开头且是命名空间方式，如abc\xyz  且\abc\xyz是bindings的key则加前缀\
				$abstract = '\\'.$abstract;
			}
			
			return $abstract;
		}

		return $this->bindings[$abstract]['concrete'];
	}
	protected function getContextualConcrete($abstract)
	{//上线文
		if (isset($this->contextual[end($this->buildStack)][$abstract]))
		{
			return $this->contextual[end($this->buildStack)][$abstract];
		}
	}

	protected function isBuildable($concrete, $abstract)
	{
		return $concrete === $abstract || $concrete instanceof Closure;
	}
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
	protected function fireCallbackArray($object, array $callbacks)
	{
		foreach ($callbacks as $callback)
		{
			$callback($object, $this);
		}
	}
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

public function build($concrete, $parameters = [])
{
	//
	if ($concrete instanceof Closure)
	{#执行闭包，并返回闭包返回的内容  ==》重点
		return $concrete($this, $parameters);
	}
	#反射类
	$reflector = new ReflectionClass($concrete);

	// If the type is not instantiable, the developer is attempting to resolve
	// an abstract type such as an Interface of Abstract Class and there is
	// no binding registered for the abstractions so we need to bail out.
	if ( ! $reflector->isInstantiable())
	{#类不能实例化
		$message = "Target [$concrete] is not instantiable.";

		throw new BindingResolutionException($message);
	}

	$this->buildStack[] = $concrete;

	$constructor = $reflector->getConstructor();#返回 ReflectionMethod 对象，不存在构造函数时返回null

	//
	if (is_null($constructor))
	{
		array_pop($this->buildStack);
		#实例化类
		return new $concrete;
	}

	$dependencies = $constructor->getParameters();#返回参数列表

	//
	$parameters = $this->keyParametersByArgument(
		$dependencies, $parameters
	);

	$instances = $this->getDependencies(
		$dependencies, $parameters
	);

	array_pop($this->buildStack);

	return $reflector->newInstanceArgs($instances);#产生一个新的实例并把$instances传给构造方法
}




