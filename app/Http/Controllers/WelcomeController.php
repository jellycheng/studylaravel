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
		echo App("jellyImage")->hello();
		echo App()->make("jellyImage")->hello();
		//echo \App\Lib\Facades\jellyImage::hello();  //配合单例做的
		echo \JellyImage::hello(); //facades调用方式

		return view('welcome');
	}


	public function yztest() {
        $data = [
            'email'=>'123@qq.com',
            'card'=>'360427233222323232', //错误的身份证号
        ];
        $validator = \Validator::make($data, [
            'email' => 'required|email',
            'card' => 'required|IdCard',
        ]);
        if ($validator->fails()){
            echo "fail"  . var_export($validator->errors()->all(), true);
        } else {
            echo 'ok';
        }

    }


}
