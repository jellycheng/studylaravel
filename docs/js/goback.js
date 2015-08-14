

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
		} else {#去触屏首页
			location.href = location.protocol+'//' +location.hostname;
		}
	}
}

