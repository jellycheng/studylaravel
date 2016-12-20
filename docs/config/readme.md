
laravel配置管理类
    Illuminate\Config\Repository.php
    实现的接口Illuminate\Contracts\Config\Repository

获取配置对象：
1. $obj = config();
2. $obj = app('config');

获取配置：
1. $config = $this->app['config']->get($key, []);
2. $val = config($key, 默认值);
3. app('config')->get($key, 默认值);

设置key 对应的值
1. config(['key1'=>值1, 'keyn'=>值n,]);
2. app('config')->set(['key1'=>值1, 'keyn'=>值n,]);

