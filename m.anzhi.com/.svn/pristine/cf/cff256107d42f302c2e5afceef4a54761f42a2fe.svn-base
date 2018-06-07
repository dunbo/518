var page = 1, lock = false;
//$.getScript("/js/anzhi-fd-min.js");
document.write(unescape("%3Cscript src='/js/anzhi-fd-min.js' type='text/javascript'%3E%3C/script%3E"));
function formore(mores,container,actions){
	 $(mores).html("<p style='text-align:center;'>正在加载<img src='/images/waiting.gif' alt='加载中' /></p>");
	 if(lock) {
         return;
      }
	lock = true;	
	$.ajax({
			type: "GET",
			url: actions+page,
			dataType: "html",
			success: function(html){
				page++;
				if(!/<(.*)>.*<\/\1>/.test(html)){
					$(mores).empty();
					$(mores).html('<a href="javascript:void(0);">没有更多！</a>');
				} else {
					$(container).after(html);
					var more = '<a href="javascript:void(0);" onclick="formore(\''+mores+'\',\''+container+'\',\''+actions+'\');">点击查看更多</a>';
					$(mores).html(more);
					lock = false;
				}
			}
	});	
}

$(document).ready(function(){
    $(".az_down_btn").live('click', down_action);
    $(".az_down_btn1").live('click', down_action);
    $(".az_down_btn4").live('click', down_action);
    $("#down_topspeed").live('click', down_action);
});

function down_action() {
    var rel = $(this).attr('rel');
    var info_arr = rel.split(',');
    var info_len = info_arr.length;
    if (info_len < 2) {
        return false;
    }
    var id = $.trim(info_arr[0]);
    var pkg = $.trim(info_arr[1]);
    var puid = 0;
    if (info_len > 2) {
        puid = $.trim(info_arr[2]);
    }
    var js_param = {type:'details', id:id, pkg:pkg, flag:1, from:2};
    var php_param = {type:'details', softid:id, puid:puid};
    Azfd.share_download(js_param, php_param);
    return false;
}

function share_download()
{
	var js_param = {};
	var php_param = {};
	if (typeof arguments[0] ==='object') {
		//参数兼容
		js_param = arguments[0];
		php_param = arguments[1];
	} else {
		var id = arguments[0];
		var pkg = arguments[1];
		var from = arguments[2];
		js_param = {type:'details', id:id, pkg:pkg, flag:1, from:from};
		php_param = {type:'details', softid:id};
	}
	Azfd.share_download(js_param, php_param);
}