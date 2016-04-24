<?php

if (defined('SWIFT_INIT_LOADED')) {#使用常量被定义，来控制不执行后面代码
    return;
}
#标记定义
define('SWIFT_INIT_LOADED', true);

// Load in dependency maps
require dirname(__FILE__).'/dependency_maps/cache_deps.php';
#require dirname(__FILE__).'/dependency_maps/mime_deps.php';
#require dirname(__FILE__).'/dependency_maps/message_deps.php';
#require dirname(__FILE__).'/dependency_maps/transport_deps.php';

// Load in global library preferences
#require dirname(__FILE__).'/preferences.php';

