


/app/Http/routes.php 应用程序路由配置
路由格式： Route::方法post|get|put|delete|any("url目录", function() { //回调方法 });
#url get请求路由，Route::get("url目录", function() { //回调方法 });
如：
Route::get('/', function()
{
    return 'Hello World';
});

#url post请求路由，Route::post("url目录", function() { //回调方法 });
Route::post('foo/bar', function()
{
    return 'Hello World';
});


为多种请求注册路由，格式：
Route::match(['get', 'post', 'put', 'delete', 'any等多种协议'], '匹配的url地址', function() { //回调方法执行的代码 });
如：
Route::match(['get', 'post'], '/', function()
{
    return 'Hello World';
});



基础路由参数，http://域名/user/123
Route::get('user/{id}', function($id)
{
    return 'User '.$id;
});



可选择的路由参数
Route::get('user/{name?}', function($name = null)
{
    return $name;
});

带默认值的路由参数
Route::get('user/{name?}', function($name = 'John')
{
    return $name;
});


使用正则表达式限制参数
Route::get('user/{name}', function($name)
{
    //
})->where('name', '[A-Za-z]+');

Route::get('user/{id}', function($id)
{
    //
})->where('id', '[0-9]+');

使用条件限制数组
Route::get('user/{id}/{name}', function($id, $name)
{
    //
})->where(['id' => '[0-9]+', 'name' => '[a-z]+']);




路由命名：
Route::get('user/profile是url地址', ['as' => 'profile路由名', function()
{
    //
}]);
Route::get('user/profile是url地址', [
    'as' => 'profile路由名', 'uses' => 'UserController控制器文件名@showProfile控制器中方法名'
]);

$name = Route::currentRouteName(); 获取当前路由名称

