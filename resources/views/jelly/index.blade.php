

@extends('jelly.jellyLayout')


定义区块： @section('title', 'Page Title')

@section('sidebar')
    @parent
	
    <p>定义区块，但不立即显示，相同区块名重写 This is appended to the master sidebar.</p>
@stop


@section('subcontent')
    <p>我在index.blade.php文件<br>， 本文件只定义各区块内容</p>
@stop

