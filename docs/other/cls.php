<?php
namespace abc\xyz;
//class_uses(类名或类对象, 是否自动加载类默认true); 返回类使用了那些trait 类
trait user {
	public function a1() {
		echo 'a1...' . PHP_EOL;
	}
}
trait user2 {
	public function a2() {
		echo 'a1...' . PHP_EOL;
	}
}
class A {
	use user;
	//use user2;

	public function aaa1() {
		echo 'aaa1...' . PHP_EOL;
	}
	
}

//var_export(class_uses('abc\xyz\A'));等价
var_export(class_uses(new \abc\xyz\A()));
/**
array (
  'abc\\xyz\\user' => 'abc\\xyz\\user',
)
*/

//array class_parents ( mixed $class类名或类对象 [, bool $autoload类不存在是是否触发自动加载器默认true] )返回类或类名对应的父类名，数组格式，跟class_uses()返回格式一样


