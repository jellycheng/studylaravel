<?php

return [
	'from'   => 'ananzu-api',
    'client' => [
        'ananzu-service' => [//服务器组
            'type'     => 'http',//请求类
            'protocol' => 'jsonrpc',//数据传输协议
            'conf'     => [
                'url' => env('DOMAIN_ANANZU_SERVICE') . '/rpc',//服务器api接口
            ],
            'accessor' => '\\App\\Services\\Accessors\\EstateServiceAccessorWithRPC',//访问器，用于发起rpc请求, 
							#访问器App\Services\Accessors\EstateServiceAccessorWithRPC(rpc客户端对象，route路由配置，'from来自ananzu-api'，‘服务器组名如ananzu-service’)
            'route'    => [//路由配置
                'showHousing'             => [//方法名
                    'sModule'  => 'HousingController', //rpc模块
                    'sMethod'  => 'show',//rpc方法
                    'sVersion' => '1.0', //版本号，是服务器控制器命名空间的一部分
                ],
				 'generateImID'            => [//方法名    $data  = AnanzuService::generateImID(业务参数array(sToken=>123))->get();
											//$oModule      = $this->oRPCClient->module($sModule=ImController);
		//$mResponse    = $oModule->{$sMethod=index}(['version' => $sVersion, 'from' => $this->sFrom, 'requestTime' => $iRequestTime, 'token' => $sToken, 'trackId' => app('trackId')->get()], $aParams=业务参数);
		//=>服务器层就路由到 App\Http\Controllers\V1.0\$sModule=ImController类的index方法(请求对象)，请求对象可以用请求对象-》get（业务参数中的key）如$request->get('sToken')
                    'sModule'  => 'ImController',
                    'sMethod'  => 'index',
                    'sVersion' => '1.0',
                ],
                'getListContract'         => [
                    'sModule'  => 'ContractController',
                    'sMethod'  => 'index',
                    'sVersion' => '1.0',
                ],
                'getListContractProgress' => [
                    'sModule'  => 'ContractProgressController',
                    'sMethod'  => 'index',
                    'sVersion' => '1.0',
                ],
                'indexwelcome'            => [
                    'sModule'  => 'WelcomeController',

                    'sMethod'  => 'index',
                    'sVersion' => '1.0',
                ],
                'storeHousing'            => [
                    'sModule'  => 'HousingController',
                    'sMethod'  => 'store',
                    'sVersion' => '1.0',
                ],
                'updateHousing'           => [
                    'sModule'  => 'HousingController',
                    'sMethod'  => 'update',
                    'sVersion' => '1.0',
                ],
                'destroyHouse'            => [
                    'sModule'  => 'HousingController',
                    'sMethod'  => 'destroy',
                    'sVersion' => '1.0',
                ],
                //房源下架时，取消掉所有待确认的合同
                'offlineHouse'            => [
                    'sModule'  => 'HousingController',
                    'sMethod'  => 'offlineHouse',
                    'sVersion' => '1.0',
                ],
                'getHousingInfos'         => [
                    'sModule'  => 'HousingController',
                    'sMethod'  => 'index',
                    'sVersion' => '1.0',
                ],
                'getHousingInfo'          => [
                    'sModule'  => 'HousingController',
                    'sMethod'  => 'show',
                    'sVersion' => '1.0',
                ],
                'getOneHouseExtByHouseID' => [
                    'sModule'  => 'HousingExtController',
                    'sMethod'  => 'getOneHouseExtByHouseID',
                    'sVersion' => '1.0',
                ],


                'getHouseExtByHouseID'    => [
                    'sModule'  => 'HousingExtController',
                    'sMethod'  => 'index',
                    'sVersion' => '1.0',
                ],

                'getResonTypeList'        => [
                    'sModule'  => 'ReportController',
                    'sMethod'  => 'getResonTypeList',
                    'sVersion' => '1.0',
                ],
                'storeResonType'          => [
                    'sModule'  => 'ReportController',
                    'sMethod'  => 'store',
                    'sVersion' => '1.0',
                ],
                'getCityList'             => [
                    'sModule'  => 'GeneralController',
                    'sMethod'  => 'getCityList',
                    'sVersion' => '1.0',
                ],
                'getOpenCityList'         => [
                    'sModule'  => 'GeneralController',
                    'sMethod'  => 'getOpenCityList',
                    'sVersion' => '1.0',
                ],
                'getCityDetailByName'     => [
                    'sModule'  => 'GeneralController',
                    'sMethod'  => 'getCityDetailByName',
                    'sVersion' => '1.0',
                ],
               

                //用户测试
                'getAllUser'              => [
                    'sModule'  => 'UserController',
                    'sMethod'  => 'test',
                    'sVersion' => '1.0',
                ],

                //获取用户扩展信息
                'getUserInfoByID'         => [
                    'sModule'  => 'UserController',
                    'sMethod'  => 'getUserInfoByID',
                    'sVersion' => '1.0',
                ],

                //添加扩展用户
                'addUser'                 => [
                    'sModule'  => 'UserController',
                    'sMethod'  => 'addUser',
                    'sVersion' => '1.0',
                ],

                //实名认证
                'realNameAuth'            => [
                    'sModule'  => 'UserController',
                    'sMethod'  => 'realNameAuth',
                    'sVersion' => '1.0',
                ],

                //用户交易明细(充值、付房租)
                'getTransactionsByUserID'            => [
                    'sModule'  => 'TransactionController',
                    'sMethod'  => 'getTransactionsByUserID',
                    'sVersion' => '1.0',
                ],

                //房东租金收入明细
                'getRentIncomeByUserID'            => [
                    'sModule'  => 'TransactionController',
                    'sMethod'  => 'getRentIncomeByUserID',
                    'sVersion' => '1.0',
                ],

                //合同相关
                'storeContract'           => [//创建合同
                    'sModule'  => 'ContractController',
                    'sMethod'  => 'store',
                    'sVersion' => '1.0',
                ],
                'getContractList'         => [//合同列表
                    'sModule'  => 'ContractController',
                    'sMethod'  => 'index',
                    'sVersion' => '1.0',
                ],
                'showContract'            => [//显示合同详情
                    'sModule'  => 'ContractController',
                    'sMethod'  => 'show',
                    'sVersion' => '1.0',
                ],
                'haveContract' =>[
                    'sModule'  => 'ContractController',
                    'sMethod'  => 'haveContract',
                    'sVersion' => '1.0',
                ],
                'signContract'            => [//确认签约合同
                    'sModule'  => 'ContractController',
                    'sMethod'  => 'signContract',
                    'sVersion' => '1.0',
                ],
                'cancelContract'          => [//取消合同
                    'sModule'  => 'ContractController',
                    'sMethod'  => 'cancelContract',
                    'sVersion' => '1.0',
                ],


                //合同订单相关
                'createOrder'             => [//创建合同
                    'sModule'  => 'ContractOrderController',
                    'sMethod'  => 'store',
                    'sVersion' => '1.0',
                ],
                'getOrderList'            => [//获取合同订单列表
                    'sModule'  => 'ContractOrderController',
                    'sMethod'  => 'index',
                    'sVersion' => '1.0',
                ],
                'payContractOrder'            => [//获取合同订单列表
                    'sModule'  => 'ContractOrderController',
                    'sMethod'  => 'payContractOrder',
                    'sVersion' => '1.0',
                ],
                'getFirstContractOrder' => [
                    'sModule'  => 'ContractOrderController',
                    'sMethod'  => 'getFirstContractOrder',
                    'sVersion' => '1.0',
                ],

                //合同操作记录日志相关
                'storeContractProgress'   => [//创建合同操作日志记录
                    'sModule'  => 'ContractProgressController',
                    'sMethod'  => 'store',
                    'sVersion' => '1.0',
                ],
                'goHomeContract' =>[
                    'sModule'  => 'ContractProgressController',
                    'sMethod'  => 'goHomeContract',
                    'sVersion' => '1.0',
                ],

                //订单、支付、充值相关
                'getOrderInfoByTradeNo'  => [    //创建充值订单
                    'sModule'  => 'OrderController',
                    'sMethod'  => 'getOrderInfoByTradeNo',
                    'sVersion' => '1.0',
                ],
                'createCharge'  => [    //创建充值订单
                    'sModule'  => 'OrderController',
                    'sMethod'  => 'createCharge',
                    'sVersion' => '1.0',
                ],
                'getOrderByContractOrderID' => [    //根据合同订单号获取订单号
                    'sModule'  => 'OrderController',
                    'sMethod'  => 'getOrderByContractOrderID',
                    'sVersion' => '1.0',
                ],
                'getOrderByTradeNo' => [    //根据合同订单号获取订单号
                    'sModule'  => 'OrderController',
                    'sMethod'  => 'getOrderByTradeNo',
                    'sVersion' => '1.0',
                ],
                'updateOrder' => [    //更新订单信息
                    'sModule'  => 'OrderController',
                    'sMethod'  => 'update',
                    'sVersion' => '1.0',
                ],
	            'collectHouse' => [ //收藏房源
		            'sModule'  => 'CollectionController',
		            'sMethod'  => 'store',
		            'sVersion' => '1.0',
	            ],
                'getCollectDetail' => [ //收藏房源
                    'sModule'  => 'CollectionController',
                    'sMethod'  => 'show',
                    'sVersion' => '1.0',
                ],

	            'collectList' => [ //收藏房源列表
		            'sModule'  => 'CollectionController',
		            'sMethod'  => 'index',
		            'sVersion' => '1.0',
	            ],
                'collectDestroy' => [ //收藏房源列表
                    'sModule'  => 'CollectionController',
                    'sMethod'  => 'destroy',
                    'sVersion' => '1.0',
                ],
                'createWithdraw' => [    //更新订单信息
                    'sModule'  => 'OrderController',
                    'sMethod'  => 'createWithdraw',
                    'sVersion' => '1.0',
                ],

            ]
        ],

        'common-service' => [
            'type'     => 'http',
            'protocol' => 'jsonrpc',
            'conf'     => [
                'url' => env('DOMAIN_COMMON_SERVICE') . '/rpc',
            ],
            'route'    => [
                'getCommonDict' => [
                    'sModule'  => 'DictController',
                    'sMethod'  => 'getListByTypes',
                    'sVersion' => '1.0',
                ],
                "getSubway"     => [
                    'sModule'  => 'SubwayLineController',
                    'sMethod'  => 'index',
                    'sVersion' => '1.0',
                ],
                "getStations"     => [
                    'sModule'  => 'SubwayStationController',
                    'sMethod'  => 'index',
                    'sVersion' => '1.0',
                ],
                //获取单条地铁站的信息
                "getStation"     => [
                    'sModule'  => 'SubwayStationController',
                    'sMethod'  => 'show',
                    'sVersion' => '1.0',
                ],
                'getRegions'    => [
                    'sModule'  => 'RegionController',
                    'sMethod'  => 'index',
                    'sVersion' => '1.0',
                ],
                'getRegion'     => [
                    'sModule'  => 'RegionController',
                    'sMethod'  => 'show',
                    'sVersion' => '1.0',
                ],
            ],
            'accessor' => '\\App\\Services\\Accessors\\EstateServiceAccessorWithRPC',
        ],

        'member-service' => [
            'type'     => 'http',
            'protocol' => 'jsonrpc',
            'conf'     => [
                'url' => env('DOMAIN_MEMBER_SERVICE') . '/v2/?access_token=4ee6854694376845b7a95abc4a4b79fc',
            ],
            'route'    => [
                //注册
                'register'          => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'register',
                ],

                //登录
                'login'             => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'login',
                ],

                //登录日志
                'createLoginLog'    => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'createLoginLog',
                ],

                //根据用户ID获取用户信息
                'getUserInfoByID'   => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'getInfoByID',
                ],

                //根据用户手机号获取用户信息
                'getInfoByMobile'   => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'getInfoByMobile',
                ],

                //换绑手机号
                'changeMobile'      => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'changeMobile',
                ],

                //修改登录密码
                'changePassword'    => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'changePassword',
                ],

                //设置登录密码
                'setPassword'       => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'setPassword',
                ],

                //获取实名认证信息
                'getAuthentication' => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'getAuthentication',
                ],

                //开始实名认证
                'authentication'    => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'authentication',
                ],

                //更改头像
                'updateAvatar'      => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'updateAvatar',
                ],

                //验证登录密码
                'checkPassword'     => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'checkPassword',
                ],

                //验证支付密码
                'checkPayPassword'  => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'checkPayPassword',
                ],

                //设置支付密码
                'updatePayPassword' => [
                    'sModule' => 'External\\User',
                    'sMethod' => 'updatePayPassword',
                ],

                //获取账户余额
                'getAccount'        => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'getAccount',
                ],

                //红包列表
                'getCoupons'        => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'getCoupons',
                ],

                //新增红包
                'addCoupons'        => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'addCoupons',
                ],

                //红包详情
                'getCouponProfile'  => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'getCouponProfile',
                ],

                //冻结红包
                'freezingCoupon'    => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'freezingCoupon',
                ],

                //解冻红包
                'unfreezingCoupon'  => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'unfreezingCoupon',
                ],

                //冻结金额
                'freezeBalance'     => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'freezeBalance',
                ],

                //解冻金额
                'unfreezeBalance'   => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'unfreezeBalance',
                ],

                //账户充值
                'charge'            => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'charge',
                ],

                //账户扣款
                'deduct'            => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'deduct',
                ],

                //银行列表
                'getBankList'            => [
                    'sModule' => 'External\\Account',
                    'sMethod' => 'getBankList',
                ],

                //用户绑定银行列表
                'getUserBankcards'            => [
                    'sModule' => 'External\\Account',
                    'sMethod' => 'getUserBankcards',
                ],

                //获取绑定的银行卡详情
                'getBankCard'            => [
                    'sModule' => 'External\\Account',
                    'sMethod' => 'getBankCard',
                ],

                //绑定银行卡
                'bindBankCard'            => [
                    'sModule' => 'External\\Account',
                    'sMethod' => 'bindBankCard',
                ],

                //解绑银行卡
                'deleteBankCard'            => [
                    'sModule' => 'External\\Account',
                    'sMethod' => 'deleteBankCard',
                ],

                //提现
                'withdraw'            => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'withdraw',
                ],

                //提现记录
                'withdrawHistory'            => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'withdrawHistory',
                ],

                //直接扣除已冻结资金
                'deductFrozen'            => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'deductFrozen',
                ],

                //使用冻结红包
                'consumingFreezedCoupon'            => [
                    'sModule' => 'External\\Anzubao',
                    'sMethod' => 'consumingFreezedCoupon',
                ],

            ],
            'accessor' => '\\App\\Services\\Accessors\\MemberServiceAccessorWithRPC',
        ],

        'loupan-service' => [
            'type'     => 'http',
            'protocol' => 'jsonrpc',
            'conf'     => [
                'url' => env('DOMAIN_LOUPAN_SERVICE') . '/rpc',
            ],
            'route'    => [
                'showLoupan' => [
                    'sModule'  => 'LoupanController',
                    'sMethod'  => 'getLoupanXq',
                    'sVersion' => '1.0',
                ],
                'getLoupanDetail' => [
                    'sModule'  => 'LoupanController',
                    'sMethod'  => 'show',
                    'sVersion' => '1.0',
                ],

            ],
            'accessor' => '\\App\\Services\\Accessors\\EstateServiceAccessorWithRPC',
        ],

        'search-service' => [
            'type'     => 'http',
            'protocol' => 'jsonrpc',
            'conf'     => [
                'url' => env('DOMAIN_SEARCH_SERVICE') . '/rpc',
            ],
            'route'    => [
                'getHouseBySearch' => [
                    'sModule'  => 'AazController',
                    'sMethod'  => 'index',
                    'sVersion' => '1.0',
                ],
                //多条件搜索小区房源数
                'getLoupanCountBySearch' => [
                    'sModule'  => 'AazController',
                    'sMethod'  => 'getListGroupByXq',
                    'sVersion' => '1.0',
                ],
                //多条件搜索板块房源书
                'getBlockCountBySearch' => [
                    'sModule'  => 'AazController',
                    'sMethod'  => 'getListGroupByBlock',
                    'sVersion' => '1.0',
                ],

            ],
            'accessor' => '\\App\\Services\\Accessors\\EstateServiceAccessorWithRPC',
        ],
    ],

    
];
