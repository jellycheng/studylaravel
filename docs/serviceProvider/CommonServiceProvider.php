<?php
namespace Jelly\Providers;

//本类写法适合php的composer类库写法
use Illuminate\Support\AggregateServiceProvider;
//在config/app.php文件providers配置key中追加 'Jelly\Providers\CommonServiceProvider',
class CommonServiceProvider extends AggregateServiceProvider {

    // protected $defer = true;

    /**
     * 多个服务提供者
     * @var array
     */
    protected $providers = [
        'Jelly\Providers\ExceptionServiceProvider',
        'Jelly\Providers\RequestClientServiceProvider',
        'Jelly\Providers\ResponseMacroServiceProvider',
        'Jelly\Providers\LogServiceProvider',
        'LogService\Providers\LogServiceProvider',
        'Jelly\Providers\ServiceAuthorizeServiceProvider',
        'Jelly\Providers\ValidatorServiceProvider',
        'Jelly\Providers\DebugServiceProvider',
        'Jelly\Providers\ConfigServiceProvider',
    ];

}