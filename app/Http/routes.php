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