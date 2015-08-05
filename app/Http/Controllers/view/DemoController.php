<?php namespace App\Http\Controllers\view;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class DemoController extends Controller {

	/**
	 * 
	 * http://localhost/learnlaravel/public/jellytestview
	 * Route::get('jellytestview', 'View\DemoController@index');
	 * @return Response
	 */
	public function index()
	{
		if (!view()->exists('emails.customer'))
		{
		    echo "emails.customer模板不存在";
		} 
		if (view()->exists('jelly.jellyLayout')) {
			echo "jelly.jellyLayout模板存在";
		}

		#return view('jelly.jellyLayout', array('jellyName'=>'jelly'));等价view('jelly.jellyLayout', ['jellyName'=>'jelly']);
		$view = view('jelly.index')->with('jellyName', 'jelly'); #传递模板变量方式1 通过第2个参数传递
		$view->with('curDate', date('Y-m-d',time()));#传递模板变量方式2 通过with(变量名，变量值)方法传递
		$view->withHi("hi模板变量值"); ##传递模板变量方式2 对象->with变量名("变量值");
		$view->with('name8', "<font color='red'>红色</font>");
		$view->with('jellyAry', [['username'=>'jelly'],['username'=>'tom'], ['username'=>'cjs']]);
		return $view;
	}

	public function demo2() {

		$view = view('jelly.demo2');
		return $view;
	}
	public function detail(Request $request)
	{
		echo($request->route('city') . '<br>');
		echo($request->route('id'));
		// $name = Route::currentRouteName();echo $name;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
