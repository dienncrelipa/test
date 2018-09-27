const SE_MINUTE_AUTO_SAVE_LOG = 60000;

(function($) {
    $.Redactor.prototype.product = function () {
        return {
            init: function() {
                // var button = this.button.addBefore('center', 'product', '商品');
                var gphoto = this.button.addAfter('image','gphoto', '画像ライブラリ');
                // this.button.addCallback(button, this.product.insert);
                this.button.addCallback(gphoto, this.product.googlePhoto);

                var that = this;

                window.triggerInsert = function(info) {
                    that.product.manualInsert(info);
                };
            },
            insert: function() {
                clearInterval(window.checkInsert);
                var left  = ($(window).width()/2)-(900/2),
                    top   = ($(window).height()/2)-(600/2),
                    popup = window.open ("/webapp/product/popup/?site_id=" + $('#cmsTargetSitesList').val(), "popup", "width=1000, height=600, top="+top+", left="+left);

            },
            manualInsert: function(info) {
                this.insert.html(info);
            },
            googlePhoto: function() {
                var left  = ($(window).width()/2)-(900/2),
                    top   = ($(window).height()/2)-(600/2),
                    popup = window.open ("/webapp/gphoto", "popup", "width=1000, height=600, top="+top+", left="+left);
            }
        }
    }

})(jQuery);

var checkRequireField = function(elm, mess){
    if(typeof $(elm) != 'undefined' && $(elm).val() == ''){
        return mess + '<br/>';
    }
    return '';
}

var checkMainKeyword = function(){
    return checkRequireField('#main_kw_tagit', '・作成した記事のキーワードを入力してください。');
}

var checkThumb = function(cls_thumb){
    return checkRequireField(cls_thumb, '・サムネイル画像を設定してください。');
}

var checkTargetSite = function(){
    return checkRequireField('#cmsTargetSitesList', '・ターゲットサイトを選んでください');
}

var checkTitlePost = function(){
    return checkRequireField('input[name="post_title"]', '・タイトルを入力して下さい。');
}

var checkDescPost = function(){
    return checkRequireField('textarea[name="post_description"]', '・リード文を入力して下さい。');
}

var checkCategory = function(){
    if($('select[name="category"]:not([disabled])').find('option[selected="selected"]').length == 0){
        return '・カテゴリーを一つ設定してください' + '<br/>';
    }
    return '';
}

var checkPostDataRequireField = function(is_public, is_create){
    var mess_errors = checkTargetSite();

    mess_errors +=  checkTitlePost();
    mess_errors +=  checkDescPost();
    mess_errors +=  is_create ? checkThumb('#feature_img_url') : checkThumb('#new_feature_img_url');
    mess_errors +=  checkMainKeyword();
    mess_errors +=  checkCategory();

    if(mess_errors != ''){
        $.confirm({
            text: mess_errors,
            title: '警告',
            cancelButton: "OK",
            confirm: function(button) {
            },
            cancel: function(button) {
            },
            confirmButtonClass: 'hide'
        });
        return false;
    }
    return true;
}

var isActionScript = function (e, className) {
    for (var i = 0; i < className.length; i++) {
        if (e.hasClass(className[i]) && isHasTagHtmlMode()) {
            showModalConfirm();
            return true;
        }
    }
};

var checkHtmlViewMode = function() {
    $('.re-html').parent().hover(function () {
        var $this   =  $(this).find('.re-html'),
            pointer = 'auto';

        if (isHasTagHtmlMode()) {
            pointer = 'none';
            showModalConfirm($this);
        }

        return setPointerEvents($this, pointer);
    });
};

var showModalConfirm = function (e, text, title, typeConfirmButton) {
    $.confirm({
        text: text ? text : 'Javascriptを使用しているため保存ができません。該当部分を削除するか、インスタグラムを再度挿入してください。警告が止まらない場合は担当者へ連絡してください。',
        title: title ? title : '警告',
        cancelButton: typeConfirmButton ? typeConfirmButton : "はい",
        confirm: function() {},
        cancel: function() {
            e ? setPointerEvents(e, 'auto') : '';
        },
        confirmButtonClass: typeConfirmButton ? typeConfirmButton : 'hide'
    });
};

var setPointerEvents = function (e, pointer) {
    e.css('pointer-events', pointer);
};

var isHasTagHtmlMode = function () {
    if (NO_SCRIPT && hasTagScript() && $('.redactor-editor').css('display') == 'none') {
        return true;
    }

    return false;
};

var instagramUrl = "platform.instagram.com",
    insEmbedSrc  = "//www.instagram.com/embed.js";

var hasTagScript = function (context) {
    const regex = /<[\s]*[\/]?[\s]*script/gi;
    context = context ? context : $('.open').val();
    if(regex.exec(context)){
        return isValidScript(instagramUrl, context) && isValidScript(insEmbedSrc, context);
    }

    return false;
};

var isValidScript = function(url, context) {
    var div = document.createElement('div');
    div.innerHTML = context;
    var script_arr = $(div).find('script');
    var verify = false;

    $.each(script_arr, function(k, script) {
        var src = $(script).attr('src');
        if(typeof src === 'undefined' || !src.includes(url)){
            return verify = true;
        }
    });

    return verify;
};