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

#http://localhost/learnlaravel/public/db/test
Route::get('db/test', function() {
    $userinfo = App\Model\User::find(2);//通过主键id查询主键id=2的记录， 根据主键取出一条数据

    $userinfo = App\Model\User::all(); //查询所有记录

    #$userinfo = App\Model\User::findOrFail(3); #根据主键取出一条数据或抛出异常
    $userinfo = App\Model\User::where('userid', '>', 1)->firstOrFail();
    $users = $userinfo->get();//返回结果集
    foreach ($users as $user)
    {
        var_dump($user->username);
    }

    #$userinfo = App\Model\User::where('userid', '>', 1)->take(10)->get(); //取前10条, 
    $num = App\Model\User::where('userid', '>', 1)->count(); //获取记录数
    //echo $num ;
    #$userinfo = App\Model\User::whereRaw('userid > ? and username = "jelly"', [1])->get();
    /**
    $user = new App\Model\User;
    $user->username = 'John';
    $user->save();#保存记录
    */
    //App\Model\User::create(['username' => 'to'.mt_rand(1,100),'pwd'=>12]);
    #$affectedRows = App\Model\User::where('votes', '>', 100)->delete(); 删除符合条件的记录

    //var_export($userinfo);


});