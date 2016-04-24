<?php


class B1 {
	protected static $instance; //静态属性，所有类对象公用,缺点也是风险: 所有子类共用一个类对象.

	public static function setInstance($container) {
		static::$instance = $container;
	}
	//这样做，启动单例的作用
	public static function getInstance()
	{
		return static::$instance;
	}

}

class A1 extends B1 {

	public function __construct() {
		//$this->setInstance($this);
		static::setInstance($this);
	}
	
	public function hello() {
		echo "A1 hello Method" . PHP_EOL;
	}

}

class A2 extends B1 {

	public function world() {
		echo 'A2 world Method' . PHP_EOL;
	}
}

$a1Obj = new A1();
B1::getInstance()->hello(); //输出 A1 hello Method
A2::getInstance()->hello();
