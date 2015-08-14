

function redirect(url) {
	  location.href = url;
}


function xss(str) {
	if (str == undefined || typeof str!='string') {
		return str;
	}
	str = str.replace(/</g, '&lt;').replace(/>/g, '&gt;');
	return str;
}




function getTime() {
	return (new Date()).getTime();
};
function randomString(length)  {
	var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');
	if (!length) {
		length = Math.floor(Math.random() * chars.length);
	}
	var str = '';
	for (var i = 0; i < length; i++) {
		str += chars[Math.floor(Math.random() * chars.length)];
	}
	return str;
}



function isNull(o) {
	return o == undefined || o == "undefined" || o == null || o == '';
}

/**
 * jsonparse
 */
function parseJSON(str) {
	try {
		return JSON.parse(str);
	} catch (e) {
		//todo
		return undefined;
	}
}


