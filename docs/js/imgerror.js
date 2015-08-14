
define('widget/imgErr', ["lib/common"], function($) {
    /**
     * @param obj={selector:'zepto选择器默认img', imgsrc:'错误图片地址,不传默认是72x72地址'}
     */
    function error(obj) {
        var obj = obj || {};
        var _img = $.url.getTouchBaseUrl() + 'static/img/imgerr/err144x144.png';
        if(obj.imgsrc) {
            _img = obj.imgsrc;
        }
        var _selector = obj.selector || 'img';
        $(_selector).one('error', function() {
            var _dataImgSrc = $(this).attr('data-imgsrc');//指定错误图片
            var _dataRemove = $(this).attr('data-removeErr');//防止重复触发
            var _dataIgnore = $(this).attr('data-ignore');//忽略绑定
            if(_dataRemove || _dataIgnore){
                return '';
            }
            if(_dataImgSrc) {
                _img = _dataImgSrc;
            }
            $.log(_img);
            $(this).attr('src', _img);
            $(this).attr('data-removeErr', 1);
        });

    }

    return {
        error:error
    }
});
