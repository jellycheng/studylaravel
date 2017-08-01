
###laravel验证类
```
https://docs.golaravel.com/docs/5.0/validation/

```

###扩展验证类，\App\Lib\Validator extends \Illuminate\Validation\Validator
```
方式1：在 App/Providers/AppServiceProvider.php文件中boot()加入以下代码：
\Validator::resolver(function ($translator, $aData, $aRules, $aMessages, $aAttributes) {
    return new \App\Lib\Validator($translator, $aData, $aRules, $aMessages, $aAttributes);
});
或者
方式2：单独建立一个服务提供者，在config/app.php文件的providers配置key中追加一个服务提供者配置'App/Providers/ValidatorServiceProvider'

使用：
$data = [
    'email'=>'123@qq.com',
    'card'=>'360427233222323232', //错误的身份证号
];
$validator = \Validator::make($data, [
    'email' => 'required|email',
    'card' => 'required|IdCard',
]);
if ($validator->fails()){
    echo "fail"  . var_export($validator->errors()->all(), true);
} else {
    echo 'ok';
}
```

