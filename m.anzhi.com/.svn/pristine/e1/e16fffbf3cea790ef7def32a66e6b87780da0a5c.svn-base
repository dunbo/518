var page = 1, lock = false;
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

var port_arr = [12345, 23456, 34567, 45678, 56789, 612345];
var market_installed = false;
var listen_failed_count = 0;
var listen_failed_max = port_arr.length;

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
    //share_download(id, pkg, 2, puid);
    var js_param = {type:'details', id:id, pkg:pkg, flag:1, from:2};
    var php_param = {type:'details', softid:id, puid:puid};
    share_download(js_param, php_param);
    return false;
}

// from=1表示来自二维码扫描，2表示来自极速下载，3表示来自分享，4表示活动分享
// js_param参数对象存放本地监听的url需要get到的参数，应在不同使用情景传不同的值
// php_param参数对象存放fast.php需要get到的参数，应在不同使用情景传不同的值
function share_download(js_param, php_param) {
    market_installed = false;
    listen_failed_count = 0;
    document.getElementById("az_spirit").innerHTML = "";
    if (!port_arr) {
        return;
    }
    var arr_len = port_arr.length;
    for (var i = 0; i < arr_len; i++) {
        add_async_download_listen_script(port_arr[i], js_param, php_param);
    }
    return;
}

function add_async_download_listen_script(port, js_param, php_param) {
    var m = document.createElement("script");
    m.type = 'text/javascript';
    var url = 'http://127.0.0.1:' + port + '/query?&callback=change_install_status&r=' + Math.floor(Math.random() * ( 1000000000 + 1));
    // 根据js_obj继续完善url
    for (var i in js_param) {
        url += '&' + i + '=' + js_param[i];
    }
    m.src = url;
    m.async = "async";
    var php_url = '/fast.php?';
    for (var i in php_param) {
        php_url += '&' + i + '=' + php_param[i];
    }
    m.onload = function() {
        if (!market_installed) {
            listen_failed_count++;
            if (listen_failed_count >= listen_failed_max) {
                window.location.href=php_url;
            }
        }
    };
    m.onerror = function() {
        listen_failed_count++;
        if (listen_failed_count >= listen_failed_max) {
            window.location.href=php_url;
        }
    };
    document.getElementById("az_spirit").appendChild(m);
}

function change_install_status() {
    market_installed = true;
}