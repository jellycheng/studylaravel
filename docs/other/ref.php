<?php

class BindingResolutionException extends Exception {}
class Ref {

	public function make($concrete, $parameters = array()) {
		return $this->build($concrete, $parameters);
	}
	//$parameters=构造方法需要的参数
	public function build($concrete, $parameters = array()) {
		
		//$concrete=字符串 则反射类
		$reflector = new ReflectionClass($concrete);//类不存在会触发php类加载器

		if ( ! $reflector->isInstantiable())
		{#类不能被可实例化
			$message = "Target [$concrete] is not instantiable.";

			throw new BindingResolutionException($message);
		}

		//反射出构造方法 ，返回ReflectionMethod 对象
		$constructor = $reflector->getConstructor();


		if (is_null($constructor))
		{#不存在构造方法即没有直接定义__construct构造方法
			//array_pop($this->buildStack);
			echo "没有直接定义__construct构造方法";
			//直接实例化类
			return new $concrete;
		}
		//反射构造方法参数,没有参数则返回空数组，有参数则返回array(0=>第1个参数的ReflectionParameter对象，1=》第2个参数的ReflectionParameter对象) 
		$dependencies = $constructor->getParameters();
		
		$parameters = $this->keyParametersByArgument(
			$dependencies, $parameters
		);

		$instances = $this->getDependencies(
			$dependencies, $parameters
		);
//var_dump($instances );
		//从堆中移除
		//array_pop($this->buildStack);
		//实例化类并返回对象，并把$instances参数给构造方法
		return $reflector->newInstanceArgs($instances);

	}

	/**
	 * If extra parameters are passed by numeric ID, rekey them by argument name.
	 *
	 * @param  array  $dependencies=反射出来的参数
	 * @param  array  $parameters=传入的参数
	 * @return array =array('参数名'=>val值，'参数名N'=>val值N，)
	 */
	protected function keyParametersByArgument(array $dependencies, array $parameters)
	{
		foreach ($parameters as $key => $value)
		{
			if (is_numeric($key))
			{
				unset($parameters[$key]);//删除第i个参数
				//$dependencies[$key]->name; #参数名
				$parameters[$dependencies[$key]->name] = $value;
			}
		}
		return $parameters;
	}

	/**
	 * Resolve all of the dependencies from the ReflectionParameters.
	 *
	 * @param  array  $parameters=反射出来的参数
	 * @param  array  $primitives=传入的参数 如array('参数名'=>val值，'参数名N'=>val值N，)
	 * @return array
	 */
	protected function getDependencies($parameters, array $primitives = array())
	{
		$dependencies = array();

		foreach ($parameters as $parameter)
		{
			$dependency = $parameter->getClass();//反射出来参数是类类型则返回ReflectionClass对象否则是null, 如果是类类型在类不存在时会触发类加载器，如果类文件不存在则报报错

			// $parameter->name为参数名
			if (array_key_exists($parameter->name, $primitives))
			{#在传入参数内，就直接用
				$dependencies[] = $primitives[$parameter->name];
			}
			elseif (is_null($dependency))
			{//普通参数，获取默认值
				$dependencies[] = $this->resolveNonClass($parameter);
			}
			else
			{//类参数则实例化
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
		{//反射参数是否存在默认值
			return $parameter->getDefaultValue();//返回参数默认值
		}
		//不存在默认值抛异常
		$message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";

		throw new BindingResolutionException($message);//BindingResolutionException
	}

	/**
	 * Resolve a class based dependency from the container.
	 *
	 * @param  \ReflectionParameter  $parameter 实例化参数类
	 * @return mixed
	 *
	 * @throws BindingResolutionException
	 */
	protected function resolveClass(ReflectionParameter $parameter)
	{
		// $parameter->getClass()->name;参数类型名即类名
		try
		{
			return $this->make($parameter->getClass()->name);//实例化类
		} catch (BindingResolutionException $e) {
			if ($parameter->isOptional())
			{
				return $parameter->getDefaultValue();
			}

			throw $e;
		}
	}

}
function __autoload($c) {echo $c;}

class A {

	public function __construct($a=1,$b=3) {

			echo 'AAAA<br>';
	}
}
class Jelly extends A{
	
	
}

class B {

	public function __construct(\stdClass $abc, $xyz=123) {
			echo $xyz;
	}
}

class C {

	public function __construct(\Jelly $abc1, $xyz1=123456) {
			echo $xyz1;
			var_dump($abc1);
	}
}

$obj = new Ref();
try{
	//$jelly = $obj->build('Jelly', array(1111, 222));
}catch(Exception $e) {
 echo $e->getMessage();
}
//$obj->build('Jelly');exit;
try{
	//$jelly = $obj->build('B', array(new stdClass));
	$jelly = $obj->build('C', array($obj->build('Jelly')));
	//$jelly = $obj->build('C');
}catch(Exception $e) {
 echo $e->getMessage();
}
