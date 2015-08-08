<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'WelcomeController@index');

Route::get('home', 'HomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);


Route::get('jellyabc', function() {
		$f = get_included_files();
		return "sdfad==jellyabc，访问地址是http://localhost/learnlaravel/public/jellyabc" . var_export($f,true);
});

Route::get('jelly', 'JellyController@index');

Route::get('jelly/tpl', 'JellyController@tplDemo');

Route::get('jelly/tpldemo1', 'JellyController@tpldemo1');

Route::get('/{city}/index/detail.id.{id}.html', 'View\DemoController@detail');
Route::get('jellytestview', 'View\DemoController@index');
Route::get('jellytestview/demo2', 'View\DemoController@demo2');

//直接路由到模板
Route::get('jellyrouteview', function()
{
	$data = ['name'=>'jelly', 'date'=>'2015.08.05'];
    return view('jelly.jellyroute', $data);
});

Route::any('foo', function()
{
    return 'Hello World';
});



Route::get('user/profile', [
	    'as' => 'profile', 'uses' => 'UserController@showProfile'
	]);

#路由群组
Route::group(['namespace' => 'V1_0', 'prefix' => '1.0'], function($router) {
    Route::get('/', 'WelcomeController@index'); #对应地址 http://localhost/learnlaravel/public/1.0
    Route::get('g1', 'G1Controller@index');#对应地址 http://localhost/learnlaravel/public/1.0/g1
});

#群组只指定url前缀情况
Route::group(['prefix' => 'admin'], function()
{
    Route::get('users', function()
    {
        echo "对应的url是http://localhost/learnlaravel/public/admin/users";
    });
});


Route::group(['prefix' => 'accounts/{account_id}'], function()
{
    Route::get('detail', function($account_id)
    {#对应的URL： http://localhost/learnlaravel/public/accounts/258/detail
        echo $account_id;
    });
});


