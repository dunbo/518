require.config({
	paths:{
		"jquery" : '//cdn.bootcss.com/jquery/2.1.4/jquery.min',
		"std":"/weixin/js/std"
	}
});

require(['jquery','std'],function($,std){
	

	var is_login = false;
	

	function buildHtml(json) {
		
		var tmp = $("#js_list_tpl").html();
		var html = "";
		for(var i=0;i < json.length; ++i) {
			//测试时如果没有这个字段手动加上
			if(typeof(json[i][21]) == "undefined") {
				json[i][21] = "";
			}
			if(json[i][6] == "已领取") {
				json[i][6] = "使用";
			}
			html += std.setTpl(tmp,
				{
					"gid":json[i][0],
					"icon":'<img src="'+ json[i][1] +'"/>',
					"name":json[i][2],
					"end_date":json[i][4],
					"get_date":std.date('Y-m-d h:i:s',json[i][8]),
					"gift_key":json[i][5]
				}
			);
		}
		return html;
	}


	var result = function(json){
		console.log(json);
		is_login = json.is_login;
		if(!json.is_login) {
			if(sessionStorage) {
				var go_login = sessionStorage.getItem("go_login");
				if(go_login != "yes") {
					sessionStorage.setItem("go_login","yes");
					std.goLogin();
				} else {
					sessionStorage.setItem("go_login","");
					history.back();
				}
				return;
			}

			std.goLogin();
			return false;
		}
		
		if(!json.data.length) {
			$("#js_main").html('<p class="giftpeck_no">尚未领取任何礼包</p>');
			return false;
		}

		var html = buildHtml(json.data);
		$("#list_box").html(html);
		
	};

	

	$.get("/weixin/api.php",{"act":"my"},result);

	var flags={canMove:true};
    

	//显示礼包码
	function showCode(code) {
		$("#js_alert_key").text(code);
		var bg_h=$("#body-bg").height()+$(document).scrollTop(),
			top_h= $("#tip-box1").height()/ 2-$(document).scrollTop();
		$("#tip-box1").css("margin-top",-top_h+"px").show();
		$("#body-bg").css("height",bg_h+"px").show();
		flags.canMove=false;
	}



	$("#list_box").on('click',"a[act=get_gift]",function(){
		var self = $(this);
		showCode(self.attr("gift_key"));
	});

	$("#js_close_alert").on("click",function() {
		$("#tip-box1").hide();
        $("#body-bg").hide();
        flags.canMove=true;
	});
	
	$("body").bind('touchmove', function (e) {
		if(!flags.canMove){
			e.preventDefault();
		}
	});
   

});







