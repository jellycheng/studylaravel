<?php
//模块配置,我自己加的,可通过 config('modules.list')获取配置文件
return [
    'list' => [
        // 目录名即模块名 => url前缀, 对应的控制器目录 App\Modules\目录名\Http\Controllers
        'User' => 'user',
        'Common' => 'common', //公共基础字典模块
    ],
];
