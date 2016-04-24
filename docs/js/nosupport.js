
function noSupportBrowser(){
	if (/MSIE (6.0|7.0|8.0)/.test(navigator.userAgent)) {
	    location.href = location.protocol + "//" + location.hostname + '/nonsupport.html';
	}
}

