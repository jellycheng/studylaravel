;(function(window){
        var jellyAppApi = {}, appVersion= '1.0.0',sceneId = '';
        //获取所在场景 1.Android app  2. Ios app  3. 触屏H5，浏览器中访问  4. 微信内访问    pajelly-android-1.0
        function getSceneid() {

            var _ua = navigator.userAgent;
            _ua = _ua.toLowerCase();
            if(navigator.userAgent.indexOf('pajelly') > -1){
                var userAgent = navigator.userAgent.split(' ');
                for(var i=0;i< userAgent.length ;i++){
                    if(userAgent[i].indexOf('pajelly') > -1) {
                        var jellyAgent = userAgent[i].split('-');
                        var agent = jellyAgent[1].toLowerCase();
                        if(agent == 'android'){
                            appVersion = jellyAgent[2];
                            jellyAppApi.sceneId = 1;
                            return 1;
                        }else if(agent == 'ios'){
                            appVersion = jellyAgent[2];
                            jellyAppApi.sceneId = 2;
                            return 2;
                        }
                    }
                }
            } else if(_ua.match(/MicroMessenger/i) == 'micromessenger'){
                //微信
                jellyAppApi.sceneId = 4;
                return 4;
            }
            jellyAppApi.sceneId = 3;
            return 3;
        }

        //获取app版本
        function getAppVersion() {
            getSceneid();
            return appVersion;
        }

        //app locaion接口配置
        var appUrl = {
            home: "pajelly://view/home?refresh=", //跳转app首页
            login: "pajelly://view/login?url=", //app登录页
            register: "pajelly://view/register?url=", //app注册页
            house:'pajelly://view/housedetail?id='  //房源详情页
        };
        //跳转到app指定页面
        function goToApp(urlType, param1, param2, extparam){
            var param1 = param1 || "";
            if(appUrl[urlType]) {
                location.href = appUrl[urlType] + '' + param1;
            }
        };

        function setTitle(){
           if(typeof window.pajelly == 'object') window.pajelly.setTitle(title);
        };


        jellyAppApi = {
            getSceneid : getSceneid,
            //设置app 标题
            setTitle:setTitle,

            goToApp:goToApp,

            init:function(){
                
            }
        }

        jellyAppApi.init();
        
        module.exports = window.jellyAppApi = jellyAppApi;
		
    })(window);
