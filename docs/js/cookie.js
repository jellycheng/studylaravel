

/**
 * cookies
 */
define('lib/cookie', function() {
    return {
        cookiePrefix : 'th5_',
        get : function(name) {
            var r = new RegExp("(^|;|\\s+)" + name + "=([^;]*)(;|$)");
            var m = document.cookie.match(r);
            return (!m ? "" : decodeURIComponent(m[2]));
        },
        getH5 : function(name) {
            return this.get(this.cookiePrefix + '' + name);
        },
        add : function(name, v, path, expire, domain) {
            var s = name + "=" + encodeURIComponent(v) + "; path=" + (path || '/') // 默认根目录
                + (domain ? ("; domain=" + domain) : '');
            if (expire > 0) {
                var d = new Date();
                d.setTime(d.getTime() + expire * 1000);
                s += ";expires=" + d.toGMTString();
            }
            document.cookie = s;
        },
        addH5 : function(name, v, path, expire, domain) {
            this.add(this.cookiePrefix + '' + name, v, path, expire, domain);
        },
        del : function(name, path, domain) {
            if (arguments.length == 2) {
                domain = path;
                path = "/"
            }
            document.cookie = name + "=;path=" + path + ";" + (domain ? ("domain=" + domain + ";") : '') + "expires=Thu, 01-Jan-70 00:00:01 GMT";
        },
        delH5 : function(name, path, domain) {
            this.del(this.cookiePrefix + '' + name, path, domain);
        }
    }
})
