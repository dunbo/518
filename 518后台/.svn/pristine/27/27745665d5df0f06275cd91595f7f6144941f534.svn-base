UE.registerUI('content_explain',function(editor,uiName){
    //创建dialog
    var dialog = new UE.ui.Dialog({
        //指定弹出层中页面的路径
        iframeUrl:'/Public/js/editorv1/dialogs/content_explain/content_explain.html',
        //需要指定当前的编辑器实例
        editor:editor,
        //指定dialog的名字
        name:uiName,
        //dialog的标题
        title:"应用内览",

        //指定dialog的外围样式
        cssRules:"width:450px;height:200px;",
        //如果给出了buttons就代表dialog有确定和取消
        // buttons:[
        //     {
        //         className:'edui-okbutton',
        //         label:'确定',
        //         onclick:function () {
        //             dialog.close(true);
        //         }
        //     },
        //     {
        //         className:'edui-cancelbutton',
        //         label:'取消',
        //         onclick:function () {
        //             dialog.close(false);
        //         }
        //     }
        // ]
    });

    var btn = new UE.ui.Button({
        name:'内览',
        title:'内览',
        //需要添加的额外样式，指定icon图标，这里默认使用一个重复的icon
        cssRules :'background-position: -781px -78px;',
        onclick:function () {
            //渲染dialog
            dialog.render();
            dialog.open();
        }
    });

    return btn;
});
