;(function(window){
        var ananzuAppApi = {}, appVersion= '1.0.0',sceneId = '';
        //获取所在场景 1.Android app  2. Ios app  3. 触屏H5，浏览器中访问  4. 微信内访问    paananzu-android-1.0
        function getSceneid() {

            var _ua = navigator.userAgent;
            _ua = _ua.toLowerCase();
            if(navigator.userAgent.indexOf('paananzu') > -1){
                var userAgent = navigator.userAgent.split(' ');
                for(var i=0;i< userAgent.length ;i++){
                    if(userAgent[i].indexOf('paananzu') > -1) {
                        var aazAgent = userAgent[i].split('-');
                        var agent = aazAgent[1].toLowerCase();
                        if(agent == 'android'){
                            appVersion = aazAgent[2];
                            ananzuAppApi.sceneId = 1;
                            return 1;
                        }else if(agent == 'ios'){
                            appVersion = aazAgent[2];
                            ananzuAppApi.sceneId = 2;
                            return 2;
                        }
                    }
                }
            } else if(_ua.match(/MicroMessenger/i) == 'micromessenger'){
                //微信
                ananzuAppApi.sceneId = 4;
                return 4;
            }
            ananzuAppApi.sceneId = 3;
            return 3;
        }

        //获取app版本
        function getAppVersion() {
            getSceneid();
            return appVersion;
        }

        //app locaion接口配置
        var appUrl = {
            home: "paananzu://view/home?refresh=", //跳转app首页
            login: "paananzu://view/login?url=", //app登录页
            register: "paananzu://view/register?url=", //app注册页
            house:'paananzu://view/housedetail?id='  //房源详情页
        };
        //跳转到app指定页面
        function goToApp(urlType, param1, param2, extparam){
            var param1 = param1 || "";
            if(appUrl[urlType]) {
                location.href = appUrl[urlType] + '' + param1;
            }
        };

        function setTitle(){
           window.paananzu.setTitle(title);
        };


        function headerInit(){
            //header logic
            var sceneId = this.sceneId,azHeaderText =  document.getElementById('J_az_header_text');

            if(sceneId == 1 || sceneId ==  2){
               azHeaderText.className += ' none';
            }else{
               var reg = new RegExp('(\\s|^)az_hidden(\\s|$)');
               azHeaderText.className = azHeaderText.className.replace(reg,' ');
            }
        };

        ananzuAppApi = {
            getSceneid : getSceneid,
            //设置app 标题
            setTitle:setTitle,

            goToApp:goToApp,

            headerInit:headerInit,

            init:function(){
                
            }
        }

        ananzuAppApi.init();
        
        module.exports = window.ananzuAppApi = ananzuAppApi;
		
    })(window);
