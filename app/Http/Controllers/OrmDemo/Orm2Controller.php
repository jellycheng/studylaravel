<?php namespace App\Http\Controllers\OrmDemo;

use App\Http\Controllers\Controller;
use Log;
use App;
class Orm2Controller extends Controller {

	public function __construct()
	{
		
	}

	/**
	 * http://localhost:8889/ormdemo/demo2
	 * @return Response
	 */
	public function index()
	{
		echo __FILE__;

	}

}
