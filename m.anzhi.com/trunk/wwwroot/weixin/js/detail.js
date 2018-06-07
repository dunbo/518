require.config({
	paths:{
		"jquery" : '//cdn.bootcss.com/jquery/2.1.4/jquery.min',
		"std":"/weixin/js/std"
	}
});

require(['jquery','std'],function($,std){

	//点击锚点刷新页面
	$(window).on('hashchange',function(e){
		setTimeout(function(){window.location.reload()},50);
	});
	var is_login = false;
	
	var gid = std.getParam("id");
	
	var result = function(json){
		//console.log(json);
		var tpl = $("#js_main").html();
		var html = "";
		var more = json.more;
		is_login = json.is_login;
		json = json.data;
		
		if(!json[0]) {
			window.location.href = "index.html";
			return false;
		}

		
		if(json[6] == "已领取") {
			json[6] = "使用";
		}
		if(typeof(json[23]) == "undefined") {
			json[23] = "";
		}
		
		var html_more = ""; 
		for(var i in more) {
			html_more += std.sprintf('<li><a href="detail.html#id=%s" >%s</a></li>',more[i][0],more[i][1]);
		}
		var pt = 0;
		json[10] = parseInt(json[10]);
		if (json[10] > 0) {
			pt = parseInt(json[7]) / parseInt(json[10]) * 100;
		}
		html += std.setTpl(tpl,
			{

				"gid":json[0],
				"icon":'<img src="'+json[1]+'"/>',
				"name":json[2],
				"detail":json[11],
				"end_date":json[5],
				"is_wb":($.inArray(json[6],["立即领取","使用"]) != -1) ? '' : 'gray',
				"status_text":json[6],
				"surplus":json[7],
				"total":json[10],
				"manual":json[13],
				"scope":json[14],
				"more":html_more,
				"redeem_time":json[12],
				"pkg":json[3],
				"gift_key":json[23],
				"pt":pt
			}
		);
		
		

		
		//console.log(html)
		//alert();
		$("#js_main").html(html);
		$("#js_main").show();

		if(html_more.length) {
			$("#js_more_box").show();
		}


		$("a[act=get_gift]").on('click',function(){
			
			
			if(!is_login){
				std.goLogin();
				return false;
			}
			
			var self = $(this);
			if(self.attr("gift_key") != "") {
				showCode(self.attr("gift_key"));
				
				return false;
			} else if($.inArray(self.text(),["立即领取","使用"]) == -1) {
				//alert("错误");
				return false;
			}

			$.get("/weixin/api.php",{"act":"get","id":gid},function(json){
				var data = json.data;
				if(data["IS_SUCESSFUL"] != 1) {
					alert("领取失败");
					return false;
				}
				console.log(json);
				//alert(data['GAME_KEY']);
				self.attr("gift_key",data['GAME_KEY'])
				self.text("使用");
				self.click();
			});
		});


	};

	var flags={canMove:true};
	function showCode(code) {
		$("#js_alert_key").text(code);
		var bg_h=$("#body-bg").height()+$(document).scrollTop(),
			top_h= $("#tip-box1").height()/ 2-$(document).scrollTop();
		$("#tip-box1").css("margin-top",-top_h+"px").show();
		$("#body-bg").css("height",bg_h+"px").show();
		flags.canMove=false;
	}

	$("#js_close_alert").on("click",function() {
		$("#tip-box1").hide();
        $("#body-bg").hide();
        flags.canMove=true;
	});

	$.get("/weixin/api.php",{"act":"detail","id":gid},result);

});