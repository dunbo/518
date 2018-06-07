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

var p_a = [12345, 23456, 34567, 45678, 56789, 612345];
var m_i = false;
var l_f_c = 0;
var l_f_m = p_a.length;

$(document).ready(function(){
    $(".az_down_btn").live('click', d_a);
    $(".az_down_btn1").live('click', d_a);
    $(".az_down_btn4").live('click', d_a);
    $("#down_topspeed").live('click', d_a);
});

function d_a() {
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
    share_download(id, pkg, 2, puid);
    return false;
}

function share_download(id, pkg, from, puid) {
    if (arguments.length < 3)
        return;
    var id = arguments[0];
    var pkg = arguments[1];
    var from = arguments[2];
    var puid = arguments[3] ? arguments[3] : 0;
    m_i = false;
    l_f_c = 0;
    document.getElementById("az_spirit").innerHTML = "";
    if (!p_a) {
        return;
    }
    var arr_len = p_a.length;
    for (var i = 0; i < arr_len; i++) {
        aadls(p_a[i], id, pkg, from, puid);
    }
    return;
}

function aadls(port, id, pkg, from, puid) {
    var m = document.createElement("script");
    m.type = 'text/javascript';
    var url = 'http://127.0.0.1:' + port + '/query?type=details&id=' + id + '&pkg=' + pkg + '&flag=1&from=' + from  + '&callback=cis&r=' + Math.floor(Math.random() * ( 1000000000 + 1));
    m.src = url;
    m.async = "async";
    m.onload = function() {
        if (!m_i) {
            l_f_c++;
            if (l_f_c >= l_f_m) {
                window.location.href="fast.php?softid=" + id + "&puid=" + puid;
            }
        }
    };
    m.onerror = function() {
        l_f_c++;
        if (l_f_c >= l_f_m) {
            window.location.href="fast.php?softid=" + id + "&puid=" + puid;
        }
    };
    document.getElementById("az_spirit").appendChild(m);
}

function cis() {
    m_i = true;
}

