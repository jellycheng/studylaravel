
var $ = require('common:zepto'),appApi = require(':app/app.js'), log = require(":common/log.js");
//引入统计模块 todo

(function() {

			function goBack() {
				 var _ref = document.referrer;
				 if(_ref) {
				    if(_ref.indexOf('/login')>-1 || _ref.indexOf('/register')>-1) {//登录注册页跳转过来的 点后退则回到触屏首页
					location.href = location.protocol+'//' + location.hostname;
				    } else {
				      history.go(-1);
				    }
				} else {
					var _uaL = navigator.userAgent.toLowerCase();
					if(_uaL.indexOf('android') > -1 && _uaL.indexOf('mb2345browser') > -1) {
						//是2345浏览器 就强制 后退，这是解决2345的坑
						history.go(-1);
					} else {//去触屏首页
						location.href = location.protocol+'//' +location.hostname;
					}
				}
			}


			function debug() {

	            if (window._debug_) {
	                log.debug.apply(this, arguments);
	            }
        
			}


			$.ananzu = {
					goBack:goBack,
					debug:debug,
					appApi:appApi
				
			};
			/**
			 * $.ananzu.debug($.ananzu.appApi.getSceneid());
			 * $.ananzu.debug($.ananzuAppApi.getSceneid());
			 * $.ananzu.debug(window.ananzuAppApi.getSceneid());
			 */
			$.ananzuAppApi = appApi;


		function _init() {
			//所有页面在通用业务逻辑
			var sceneid = $.ananzu.appApi.getSceneid();
			if(sceneid==3) {
				$('.J_az_header').show();
			} else {
				//app内
				$('.J_az_header').hide();

			}
			//通过class绑定通用返回事件
			$('.J_az_goback').on('click', function() {
				goBack();

			});

		}
		$(function(){

			_init();	
		});
	module.exports = $;
})();
