<?php
namespace Jelly;

class Route {

	public function test1() {
	
		echo 'test1--fun';
	}	

	public function test2($param) {
	
		echo 'test2--fun' . print_r($param, true);
	}
}
