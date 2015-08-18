<?php
header("Content-type: text/html; charset=utf-8");

//测试config配置类--模块

spl_autoload_register(
  function ($class) {
      static $classes = NULL;
      static $path = NULL;

      if ($classes === NULL) {
		  $path = dirname(__FILE__);//当前文件目录

          $classes = array(#配置可以自动加载的类名对应的文件
            'jelly\\config\\repository' => $path . '/lib/Repository.php',
            'jelly\\config\\repositoryinterface' => $path . '/lib/RepositoryInterface.php',
          );

          
      }
      $cn = strtolower($class);
      if (isset($classes[$cn])) {#在配置中 
          require $classes[$cn];
      }
  }
);

$data = array('log' => 'daily',
				'timezone' => 'PRC',
				'debug' =>true,
				'url' => 'http://localhost',
			);
//配置类对象
$configObj = new Jelly\Config\Repository($data);



//设置配置信息
$configObj->set('a.b.c', "你好abc");
$configObj->set('host', "127.0.0.1");
$configObj['xyz'] = '设置xyz的值';

echo 'a.b.c=' . $configObj->get('a.b.c') . '<br>';
echo 'a.b.c=' . $configObj['a.b.c'] . '<br>';

echo "所有配置信息：<br>";
$allCfg = $configObj->all();
var_export($allCfg);
