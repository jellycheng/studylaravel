<?php namespace App\Http\Controllers\OrmDemo;

use App\Http\Controllers\Controller;
use Log;
use App;
class Orm1Controller extends Controller {

	public function __construct()
	{
		
	}

	/**
	 * 查询相关sql
	 * http://localhost:8889/ormdemo/index
	 */
	public function index()
	{
		echo "<pre>";
		$loginStatus = \App\Model\LoginStatus::find(10); //根据主键id 获取一条记录
		//var_export($loginStatus);
		echo PHP_EOL . 'find sToken: ' . $loginStatus->sToken . '' . PHP_EOL; //获取sToken字段值

		//指定查询条件
		$loginStatusMore = \App\Model\LoginStatus::where('iAutoID', '>', 100)->take(10)->get();//返回Illuminate\Database\Eloquent\Collection类对象，多条结果集
		//var_export($loginStatusMore);
		foreach ($loginStatusMore as $loginStatusOne)
		{	//循环结果集（多条记录）， $loginStatusOne=App\Model\LoginStatus类对象
			echo 'get sToken: ' . $loginStatusOne->sToken . '' . PHP_EOL; //获取每条记录的token
		}

		$count = \App\Model\LoginStatus::where('iAutoID', '>', 100)->count();
		echo "符合条件的记录数： {$count} ";


		//获取一条记录
		$dataOne = \App\Model\LoginStatus::where('iAutoID', '<=', 100)->first();//App\Model\LoginStatus类对象
		#var_dump($dataOne);
		var_export($dataOne->toArray());//数据转为数组， model对象->toJson();数据转为json格式
		/**
		array (
			'iAutoID' => 1,
			'iUserID' => 45,
			'sToken' => '2d130d695599073a21b844298cded13f',
			'iType' => 1,
			'iStatus' => 0,
			'iCreateTime' => 1473314384,
		)
		 */
	}

	/**
	 * 插入相关sql
	 * http://localhost:8889/ormdemo/insert
	 */
	public function insert() {
		echo "<pre>";
		$loginStatusObj = new \App\Model\LoginStatus;
		$loginStatusObj->iUserID = mt_rand(100, 999);
		$loginStatusObj->sToken= mt_rand(1000, 9999) . "-laravel";
		$b = $loginStatusObj->save();//返回bool值
		var_dump($b);
		echo "主键名： " .$loginStatusObj->getKeyName(); //获取主键名
		echo "插入的记录自增id: " . $loginStatusObj->iAutoID . PHP_EOL; //获取插入的记录自增id
		echo "插入的记录自增id: " . $loginStatusObj->getKey() . PHP_EOL; //等价 $loginStatusObj->getKey();
		echo "插入的记录自增id： " . $loginStatusObj->getAttribute('iAutoID') . PHP_EOL; //$loginStatusObj->getAttribute('字段名');获取字段值

	}

}
