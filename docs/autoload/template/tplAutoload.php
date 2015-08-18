<?php
//自动加载器，匿名写法，
spl_autoload_register(
  function ($class) {
      static $classes = NULL;
      static $path = NULL;

      if ($classes === NULL) {
		  $path = dirname(__FILE__);//当前文件目录

          $classes = array(#配置可以自动加载的类名对应的文件
            'text_template' => $path . '/Template.php'
          );

          
      }
      $cn = strtolower($class);
      if (isset($classes[$cn])) {#在配置中 
          require $classes[$cn];
      }
  }
);

