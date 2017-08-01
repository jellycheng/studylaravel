<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
/**
 * 扩展验证类 服务提供者,在config/app.php的providers配置key中追加'App/Providers/ValidatorServiceProvider'
 */
class ValidatorServiceProvider extends ServiceProvider
{

    /**
     * 扩展验证规则
     */
    public function boot()
    {
        Validator::resolver(function ($oTranslator, $aData, $aRules, $aMessages, $aAttributes) {
            return new \App\Lib\Validator($oTranslator, $aData, $aRules, $aMessages, $aAttributes);
        });
    }

    public function register()
    {

    }

}
