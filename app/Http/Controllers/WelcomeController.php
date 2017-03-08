<?php namespace App\Http\Controllers;


use Log;
use App;
class WelcomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest');
	}

	/**
	 * http://localhost:8889/?open_log=1
	 * @return Response
	 */
	public function index()
	{
		if(isset($_GET['open_log']) && $_GET['open_log']==1) {
			Log::info('这是我写的日志，日志文件在storage/logs/laravel-年-月-日.log');
		}
		return view('welcome');
	}

}
