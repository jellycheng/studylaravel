

//设备场景
function shebei() {
	 var _os=9,	//操作系统 1.Android, 2. Ios   8.windowsPhone, 9.其他
	     _ua = navigator.userAgent,
		_isweixin=0; //是否微信

	 if(_ua.indexOf("Mobile") > -1 && window.screen.width < 800){
		//小于800屏幕的移动设备
		 if(_ua.indexOf("iPhone") > -1){
			 _os = 2;
		 } else if(_ua.indexOf("NokiaN") > -1){//诺基亚
		 	 _os = 9;
		 } else if(_ua.indexOf("Android") > -1 || _ua.indexOf("Linux") > -1){
		 	 _os = 1;
		 } else if(_ua.indexOf("Windows Phone") > -1){
		 	 _os = 8;
		 }
     }
     if(_ua.toLowerCase().match(/MicroMessenger/i) == 'micromessenger'){
         //是否在微信场景
         _isweixin = 3;
     }
	 return {
		 ua: _ua,
		 os: _os,
		 isweixin: _isweixin
	 }

}



