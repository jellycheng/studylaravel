<?php namespace App\Http\Controllers\jelly;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class UserController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}
	#get请求 http://localhost/learnlaravel/public/jellyusers
	public function getIndex() {

		echo __FILE__;
	}

	#get请求 http://localhost/learnlaravel/public/jellyusers/tom
	public function getTom() {

		echo  'get method tom';
	}


	#get请求 http://localhost/learnlaravel/public/jellyusers/admin-profile
	public function getAdminProfile() {

		echo  'http://localhost/learnlaravel/public/jellyusers/admin-profile  破折号转成驼峰式方法名';
	}

	#post请求 http://localhost/learnlaravel/public/jellyusers/tom
	public function postTom() {

		echo __FILE__ . '  post tom';
	}

	public function anyLogin() {

		echo "anyLogin, post or get ";
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
