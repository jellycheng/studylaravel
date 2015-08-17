<?php

class A {

	public function cName() {
		$class = get_class($this);
		echo $class.'<br>';
	}
}


class B extends A {

	public function hello() {
		//$class = get_class($this);
		echo 'hello<br>';
	}
}


$aObj = new A();
echo $aObj->cName();//A


$bObj = new B();
echo $bObj->cName(); //B
