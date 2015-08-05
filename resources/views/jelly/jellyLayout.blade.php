<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>这是我的布局文件layout-父- @yield('title')</title>
 </head>
 <body>
  <h1>Hello, <?php echo $jellyName; ?></h1>
  <pre>
  	日期： {{$curDate}}
  	hi：	{{ $hi }}
	时间戳： {{ time() }}:{{ date('Y-m-d', time()) }}
	{{ isset($name) ? $name : 'Default' }}

	@{{ 这块内容不会被@curDate blade模板引擎解析 }}
  </pre>
<pre>
视图模板根目录： /resources/views/

在控制器层和Route配置调用view层示例： 
	return view('模板目录.模板文件名', $data传给模板的变量-数组格式);	
	如return view('userinfo', ['name' => 'Jelly']);

路由方式：
	Route::get('user/info', function()
	{
	    return view('userinfo', ['name' => 'Jelly']);  #对应的模板文件 /resources/views/userinfo.blade.php
	});
控制器方式：
	#return view('jelly.jellyLayout', array('jellyName'=>'jelly'));等价view('jelly.jellyLayout', ['jellyName'=>'jelly']);
		$view = view('jelly.jellyLayout')->with('jellyName', 'jelly'); #传递模板变量方式1 通过第2个参数传递
		$view->with('curDate', date('Y-m-d',time()));#传递模板变量方式2 通过with(变量名，变量值)方法传递
		$view->withHi("hi模板变量值"); ##传递模板变量方式2 对象->with变量名("变量值");
		return $view;

{!! $name8 !!} 
{{ $name8 }} 
	

</pre>

@section('sidebar')
    定义sidebar区别并显示，This is the master sidebar. 区块1<br>
@show

==============================================<br>
@yield('sidebar')

@yield('subcontent')


@yield('section123', '<font color="red">Default Content，区块section123不存在</font>')

<br>

@if (isset($jellyif) && count($jellyif) === 1)
    <font color="red">if条件1为真</font>
@elseif (isset($jellyif) && count($jellyif) > 1)
   <font color="red">if条件2为真</font>
@else
    <font color="red">if条件不为真</font>
@endif


@unless (isset($a123) && $a123)
    <br><font color="red">unless条件不为真</font>
@endunless

@include('jelly.sub.subIndex', ['name' => '{{$name}}', 'date'=>'$date', 'abc'=>'1abc1'])


@foreach($jellyAry as $k=>$v)
	key = {{ $k }} <br>
	username = {{ $v['username'] }} <br>
@endforeach

 </body>
</html>
