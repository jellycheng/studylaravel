<?php
namespace abc\xyz;
/**
 * 反射类 demo
 */
class A1 {

    public function __construct()
    {

    }

    public function getAaa() {
        return 'aaa...' . PHP_EOL;
    }
}
$concrete = 'abc\xyz\A1';
$a1Obj = ref1($concrete);
echo $a1Obj->getAaa();

function ref1($concrete) {
    $reflector = new \ReflectionClass($concrete);

    if ( ! $reflector->isInstantiable()) {#类不能被可实例化
        echo "类 [$concrete] 不能实例化" . PHP_EOL;
        return '';
    }
    //获取类的构造方法 ，返回ReflectionMethod 对象
    $constructor = $reflector->getConstructor();
    if (is_null($constructor)) {
        echo "不存在构造方法, 直接实例化类" . PHP_EOL;
        return new $concrete;
    }
    $dependencies = $constructor->getParameters(); //反射构造函数可接收的所有参数

    $instances = [];
    //实例化类返回类对象(把$instances参数给构造方法,执行构造函数)
    return $reflector->newInstanceArgs($instances);

}



