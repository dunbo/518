UE.registerUI('linkimage',function(editor,uiName){
    //创建dialog
    var dialog = new UE.ui.Dialog({
        //指定弹出层中页面的路径
        iframeUrl:'/Public/js/editorv1/dialogs/linkimage/linkimage.html',
        //需要指定当前的编辑器实例
        editor:editor,
        //指定dialog的名字
        name:uiName,
        //dialog的标题
        title:"外链图片",

        //指定dialog的外围样式
        cssRules:"width:652px;height:450px;",
        //如果给出了buttons就代表dialog有确定和取消
     /*   buttons:[
            {
                className:'edui-okbutton',
                label:'确定',
                onclick:function () {
                    dialog.close(true);
                }
            },
            {
                className:'edui-cancelbutton',
                label:'取消',
                onclick:function () {
                    dialog.close(false);
                }
            }
        ]*/
    });

    var btn = new UE.ui.Button({
        name:'外链图片',
        title:'外链图片',
        //需要添加的额外样式，指定icon图标，这里默认使用一个重复的icon
        cssRules :'background-position: -754px -77px;',
        onclick:function () {
            //渲染dialog
            dialog.render();
            dialog.open();
        }
    });

    return btn;
});
//检查是否还存在第三方图片，并替换cdn域名
function checkRemote(){
    var me = UE.getEditor('ueditor'),
    catcherLocalDomain = me.getOpt('catcherLocalDomain');
    var imgDomain = me.getOpt('imgDomain') ? me.getOpt('imgDomain') : location.host;
    var unUsePath = me.getOpt('unUsePath') ;
    var con = me.getContent();

    var remoteImages = [],
    imgs = UE.dom.domUtils.getElementsByTagName(me.document, "img"),
    test = function (src, urls) {
        if (src.indexOf(location.host) != -1 ||src.indexOf(imgDomain) != -1 || /(^\.)|(^\/)/.test(src)) {
            var newsrc = src.replace(location.host + unUsePath, imgDomain);
            if(src != newsrc){
                con = con.replace(src,newsrc);
            }
            return true;
        }
        if (urls) {
            for (var j = 0, url; url = urls[j++];) {
                if (src.indexOf(url) !== -1) {
                    return true;
                }
            }
        }
        return false;
    };

    for (var i = 0, ci; ci = imgs[i++];) {
        if (ci.getAttribute("word_img")) {
            continue;
        }
        var src = ci.getAttribute("_src") || ci.src || "";
        if (/^(https?|ftp):/i.test(src) && !test(src, catcherLocalDomain)) {
            remoteImages.push(src);
        }
    }
    if(remoteImages.length>0){
        alert('存在外链图片，为避免图片失效，请上传为本地图片');
        return false;
    }else{
        me.setContent(con);
    }
    return true;


}