<?php
if (class_exists('Xxx', false)) {#类存在则不执行以后的代码，否则执行。且类不存在时是不触发自动加载器的
	#避免了类不重复定义的错误
    return;
}

//加载类
require dirname(__FILE__).'/classes/Xxx.php';

if (!function_exists('_Xxxmailer_init')) {#方法不存在则定义方法
    function _Xxxmailer_init()
    {
        require dirname(__FILE__).'/Xxx_init.php';
    }
}

//注册自动加载器
Xxx::registerAutoload('_Xxxmailer_init');
