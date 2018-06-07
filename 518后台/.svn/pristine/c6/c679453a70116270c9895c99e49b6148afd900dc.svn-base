(function () {
 	window.onload = function () {
     	initImg('queueList');
     	initEvent('filelist')
    };

	var me = editor,
    ajax = UE.ajax;
    function initEvent(target){
		var container = document.getElementById(target)
		// console.log(container);
        var _this = this;
        /* 选中图片 */
        domUtils.on(container, 'click', function (e) {
            var target = e.target || e.srcElement,
                li = target.parentNode.parentNode;
            if (li.tagName.toLowerCase() == 'li') {
            	if(domUtils.hasClass(li, 'success') ||domUtils.hasClass(li, 'error'))
            		return true;
                if (domUtils.hasClass(li, 'selected')) {
                    domUtils.removeClasses(li, 'selected');
                    var cnt = $("#ckcnt").text();
                    cnt = parseInt(cnt);
                    $("#ckcnt").text(cnt-1);
                } else {
                    domUtils.addClass(li, 'selected');
                    var cnt = $("#ckcnt").text();
                    cnt = parseInt(cnt);
                    $("#ckcnt").text(cnt+1);
                }
            }
            
        });

      /* 点击全选按钮 */
        domUtils.on($G('selet'), 'click', function(){
            var key = $G('selet').checked;
            $("#ckcnt").text('0');
            if(key) {
                $('#filelist').find('li').each(function(){
                	if(!$(this).hasClass('success') && !$(this).hasClass('errror')){
                        $(this).addClass('selected');
                        var cnt = $("#ckcnt").text();
                        cnt = parseInt(cnt);
                        $("#ckcnt").text(cnt+1);
                    }
                });
            }else{
            	$('#filelist').find('li').each(function(){
            		if(!$(this).hasClass('success') && !$(this).hasClass('errror')){
                	    $(this).removeClass('selected');
                    }
                });
            }
        });
          /* 点击上传按钮 */
        domUtils.on($G('searchBtn'), 'click', function(){
        	var jobj = {};
            $('#filelist').find('li.selected').each(function(){
            	var key = $(this).attr('id'),value = $(this).find('img').attr('src');
            	jobj[key] = value;
            });

            if(JSON.stringify(jobj) == '{}') return false;
            $('#uploading').show();
            $.post(me.getActionUrl('uploadRemote'),jobj,function(data,status){
                $('#uploading').hide();
            	var obj = JSON.parse(data);
                var suc = err = size = 0;
                var con = editor.getContent();
                /* 获取源路径和新路径 */
                var imgs = UE.dom.domUtils.getElementsByTagName(me.document, "img");
                var i, j, ci, cj, oldSrc, newSrc, list = obj;

                for (i = 0; ci = imgs[i++];) {
                    oldSrc = ci.getAttribute("_src") || ci.src || "";
                    for(j in list){
                        if (oldSrc == list[j].searchurl) {
                            if(list[j].code == 200){
                                //抓取失败时不做替换处理
                                newSrc = list[j].url;
                                $("#"+j).attr('class','success');
                                suc += 1;
                                size += list[j].size;
                                UE.dom.domUtils.setAttributes(ci, {
                                    "src": newSrc,
                                    "_src": newSrc,
                                    "width":list[j].width,
                                    "height":list[j].height,
                                    "dataoriginal":newSrc,
                                    "class":'lazy'
                                });
                            }else{
                                $('<p class="error">'+list[j].message+'</p>').show().appendTo($("#"+j));
                                $("#"+j).attr('class','error');
                                err += 1;

                            }
                            break;
                        }
                    }
                }


            /*  $.each(obj,function(key,value){
                    if(value.code != 200){
                        $('<p class="error">'+value.message+'</p>').show().appendTo($("#"+key));
                        $("#"+key).attr('class','error');
                        err += 1;
                    }else{
                        $("#"+key).attr('class','success');
                        con = con.replace(jobj[key],value.url)
                        suc += 1;
                        size += value.size;
                    }

                });
                editor.setContent(con);*/
                size = formatSize(size,2);
                $("#ckcnt").text(0);
                $("#uptips").text('成功'+suc+';失败'+err+';大小:'+size);
            });


           
        });
        
    }

    function formatSize ( size, pointLength, units ) {
        var unit;

        units = units || [ 'B', 'K', 'M', 'G', 'TB' ];

        while ( (unit = units.shift()) && size > 1024 ) {
            size = size / 1024;
        }

        return (unit === 'B' ? size : size.toFixed( pointLength || 2 )) +
                unit;
    }
    function initImg (target) {
    	this.$wrap = target.constructor == String ? $('#' + target) : $(target);
	    // $statusBar = $wrap.find('.statusBar'),
	    $queue = $wrap.find('.filelist'),
        // 文件总体选择信息。
        // $info = $statusBar.find('.info'),
        // 上传按钮
        $upload = $wrap.find('.uploadBtn'),
        // 上传按钮
        $filePickerBtn = $wrap.find('.filePickerBtn'),
        // 上传按钮
        // $filePickerBlock = $wrap.find('.filePickerBlock'),
        // 没选择文件之前的内容。
        $placeHolder = $wrap.find('.placeholder');
        // 总体进度条
        // $progress = $statusBar.find('.progress').hide();

        var catcherLocalDomain = me.getOpt('catcherLocalDomain'),
            catcherActionUrl = me.getActionUrl(me.getOpt('catcherActionName')),
            catcherUrlPrefix = me.getOpt('catcherUrlPrefix'),
            catcherFieldName = me.getOpt('catcherFieldName');
        var imgDomain = me.getOpt('imgDomain') ? me.getOpt('imgDomain') : location.host;

        var remoteImages = [],
        imgs = UE.dom.domUtils.getElementsByTagName(me.document, "img"),
        test = function (src, urls) {
            if (src.indexOf(location.host) != -1 || src.indexOf(imgDomain) != -1 || /(^\.)|(^\/)/.test(src)) {
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
        if (remoteImages.length) {
            addFile(remoteImages)

        }

        function addFile(srcs) {

        	if(!srcs.length) return false;
        	var id = 1;
    		$placeHolder.addClass('element-invisible');
            // $statusBar.show();
        	for (src in srcs) {
		   		 var $li = $('<li id="oImg_' + id + '">' +
		            '<p class="title">oImg_' + id + '</p>' +
		            '<p class="imgWrap"></p>' +
		            '<p class="progress"><span></span></p>' +
		            '</li>'),

		        $btns = $('<div class="file-panel">' +
		            '<span class="cancel">' + lang.uploadDelete + '</span>' +
		            '</div>').appendTo($li),
		        $prgress = $li.find('p.progress span'),
		        $wrap = $li.find('p.imgWrap'),
		        //$info = $('<p class="error"></p>').hide().appendTo($li),
		    	$img = $('<img src="' + srcs[src] + '" width="113px" height="113px">');
	  			$wrap.empty().append($img);
	  			var $icon = $('<span class="icon">');
	  			$wrap.append($icon);
		 	   	// $li.insertBefore($filePickerBlock);
                $queue.append($li);
		 	   	id += 1;
			}
			$placeHolder.addClass('element-invisible');
            $queue.removeClass('element-invisible');
            // $statusBar.removeClass('element-invisible');
            // $progress.hide(); $info.show();
		}

    };
})();