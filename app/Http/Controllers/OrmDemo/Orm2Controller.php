<?php namespace App\Http\Controllers\OrmDemo;

use App\Http\Controllers\Controller;
use Log;
use App;
class Orm2Controller extends Controller {

	public function __construct()
	{
		
	}

	/**
	 * http://localhost:8889/ormdemo/index2
	 */
	public function index()
	{
		echo __FILE__;

	}
	//http://localhost:8889/ormdemo/demo2
	public function demo() {
		//模型类::xxx方法();查找顺序如下：
		//先查询模型类是否存在静态方法-》模型类是否存在保护or私有的方法-》
		//->\Illuminate\Database\Eloquent\Builder 类的方法()->Illuminate\Database\Query\Builder 类的方法()
		$newQuery = \App\Model\LoginStatus::hi123("abc.xyz");
		var_dump($newQuery);

	}

}
