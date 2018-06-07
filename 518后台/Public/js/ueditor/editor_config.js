/**
 *  ueditor完整配置项
 *  可以在这里配置整个编辑器的特性
 */

/**************************提示********************************
 * 所有被注释的配置项的默认值就是注释项后边给的值或者是有提示默认值是多少，如果你想改，就去掉注释，修改默认值，或者在实例化编辑器时传入相应的值，就会覆盖默认的配置值
 * 每次升级编辑器时，可以直接用你改的editor_config.js直接覆盖新版本的editor_config.js,而不用担心会出现错误提示
 **************************提示********************************/


(function () {
    /**
     * 编辑器资源文件根路径。它所表示的含义是：以编辑器实例化页面为当前路径，指向编辑器资源文件（即dialog等文件夹）的路径。
     * 鉴于很多同学在使用编辑器的时候出现的种种路径问题，此处强烈建议大家使用"相对于网站根目录的相对路径"进行配置。
     * "相对于网站根目录的相对路径"也就是以斜杠开头的形如"/myProject/ueditor/"这样的路径。
     * 如果站点中有多个不在同一层级的页面需要实例化编辑器，且引用了同一UEditor的时候，此处的URL可能不适用于每个页面的编辑器。
     * 因此，UEditor提供了针对不同页面的编辑器可单独配置的根路径，具体来说，在需要实例化编辑器的页面最顶部写上如下代码即可。当然，需要令此处的URL等于对应的配置。
     * window.UEDITOR_HOME_URL = "/xxxx/xxxx/";
     */
    var URL;

    /**
     * 此处配置写法适用于UEditor小组成员开发使用，外部部署用户请按照上述说明方式配置即可，建议保留下面两行，以兼容可在具体每个页面配置window.UEDITOR_HOME_URL的功能。
     */
    var tmp = window.location.pathname;
        URL = window.UEDITOR_HOME_URL||"/Public/js/ueditor/";//这里你可以配置成ueditor目录在您网站的相对路径或者绝对路径（指以http开头的绝对路径）

    /**
     * 配置项主体。注意，此处所有涉及到路径的配置别遗漏URL变量。
     */
    window.UEDITOR_CONFIG = {

        //为编辑器实例添加一个路径，这个不能被注释
        UEDITOR_HOME_URL : URL

        //工具栏上的所有的功能按钮和下拉框，可以在new编辑器的实例时选择自己需要的从新定义
        ,toolbars:[
            ['ClearDoc', 'Bold', 'Italic', 'Underline', 'JustifyLeft', 'JustifyCenter', 'InsertImage', 'ImageNone', 'ImageLeft', 'ImageCenter', 'ImageRight']
        ]
        //当鼠标放在工具栏上时显示的tooltip提示
        ,labelMap:{
            'cleardoc':'清空文档','bold':'加粗','italic':'斜体','underline':'下划线','justifyleft':'居左对齐','justifycenter':'居中对齐','insertimage':'插入图片','imagenone':'默认','imageleft':'图片居左','imagecenter':'图片居中','imageright':'图片居右'
        }

        //常用配置项目
        //,isShow : true    //默认显示编辑器

        //,initialContent:'欢迎使用ueditor!'    //初始化编辑器的内容,也可以通过textarea/script给值，看官网例子

        //,autoClearinitialContent:false //是否自动清除编辑器初始内容，注意：如果focus属性设置为true,这个也为真，那么编辑器一上来就会触发导致初始化的内容看不到了

        //,iframeCssUrl: URL + '/themes/default/iframe.css' //给编辑器内部引入一个css文件

        //,textarea:'editorValue' // 提交表单时，服务器获取编辑器提交内容的所用的参数，多实例时可以给容器name属性，会将name给定的值最为每个实例的键值，不用每次实例化的时候都设置这个值

        //,focus:true //初始化时，是否让编辑器获得焦点true或false

        //,minFrameHeight:320  // 最小高度,默认320

        //,autoClearEmptyNode : true //getContent时，是否删除空的inlineElement节点（包括嵌套的情况）

        //,fullscreen : false //编辑器初始化结束后,编辑区域是否是只读的，默认是false

        //,readonly : false //编辑器层级的基数,默认是999

        //,zIndex : 999 //图片操作的浮层开关，默认打开

        //,imagePopup:true

        //,initialStyle:'body{font-size:18px}'   //编辑器内部样式,可以用来改变字体等

        //,emotionLocalization:false //是否开启表情本地化，默认关闭。若要开启请确保emotion文件夹下包含官网提供的images表情文件夹

        //,enterTag:'p' //编辑器回车标签。p或br

        //,pasteplain:false  //是否纯文本粘贴。false为不使用纯文本粘贴，true为使用纯文本粘贴

        //iframeUrlMap
        //dialog内容的路径 ～会被替换成URL,垓属性一旦打开，将覆盖所有的dialog的默认路径
        //,iframeUrlMap:{
        // 'anchor':'~/dialogs/anchor/anchor.html',
        // }

		//tab
        //点击tab键时移动的距离,tabSize倍数，tabNode什么字符做为单位
        //,tabSize:4
        //,tabNode:'&nbsp;'

		//insertimage
		//图片上传配置区
        ,imageUrl:"http://" + window.location.host + "/index.php/Sl/Activate/article_img_add"         //图片上传提交地址
        ,imagePath:"http://192.168.0.99/testdata/"                 //图片修正地址，引用了fixedImagePath,如有特殊需求，可自行配置
        //,imageFieldName:"upfile"                   //图片描述的key,若此处修改，需要在后台对应文件修改对应参数
        //,compressSide:0                            //等比压缩的基准，确定maxImageSideLength参数的参照对象。0为按照最长边，1为按照宽度，2为按照高度
        //,maxImageSideLength:900                    //上传图片最大允许的边长，超过会自动等比缩放,不缩放就设置一个比较大的值，更多设置在image.html中

		//图片在线管理配置区
        ,imageManagerUrl:URL + "php/imageManager.php"       //图片在线管理的处理地址
        ,imageManagerPath:URL + "php/"                                    //图片修正地址，同imagePath
		
    };
})();
