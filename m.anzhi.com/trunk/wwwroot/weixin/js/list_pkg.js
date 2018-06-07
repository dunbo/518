require.config({
	paths:{
		"jquery" : '//cdn.bootcss.com/jquery/2.1.4/jquery.min',
		"std":"/weixin/js/std"
	}
});

require(['jquery','std'],function($,std){
	var is_login = false;
	

	function buildHtml(json) {
		
		var tmp = '<li>'+
			'<div class="app_icon">'+
				'<img src="${icon}"/>'+
			'</div>'+
			'<div class="app_info">'+
				'<h4>${name}</h4>'+
				'<p class="app_time">截止时间：${end_date}</p>'+
				'<div class="app_count">'+
					'<p><span style="width:${pt}%"></span></p>'+
					'<em>剩余：${surplus}/${total}</em>'+
				'</div>'+
				'<a href="javascript:void(0);" class="app_btn ${is_wb}" act="get_gift" gift_key="${gift_key}" gid="${gid}">${lbztwb}</a>'+
				'<a href="detail_new.html#id=${gid}" class="click_area"></a>'+
			'</div>'+
		'</li>';
		var html = "";
		for(var i=0;i < json.length; ++i) {
			//测试时如果没有这个字段手动加上
			if(typeof(json[i][21]) == "undefined") {
				json[i][21] = "";
			}
			if(json[i][6] == "已领取") {
				json[i][6] = "使用";
			}
			var pt = 0;
			json[i][10] = parseInt(json[i][10]);
			if (json[i][10] > 0) {
				pt = parseInt(json[i][7]) / parseInt(json[i][10]) * 100;
			}
			html += std.setTpl(tmp,
				{
					"gid":json[i][0],
					"icon":json[i][1],
					"name":json[i][2],
					"end_date":json[i][5],
					"is_wb":(json[i][6] == "发放完毕") ? 'app_gray' : '',
					"lbztwb":json[i][6],
					"surplus":json[i][7],
					"total":json[i][10],
					"gift_key":json[i][21],
					"pt":pt
				}
			);
		}
		return html;
	}


	//加载更多
	$("#js_more a").on("click",function(){
		
		var $self = $(this);

		if($self.attr("Available") == 1) {
			return false;
		}
		$self.attr("Available",1);
		var html = '<div class="app_loading">'+
				'<i>正在加载</i>'+
				'<img src="http://m.anzhi.com/images/loading.gif"/>'+
				'<div class="clear"></div>'+
			'</div>';

		$self.html(html);
		
		
		var index = $("#list_box").children().length;
		
		var type = std.getParam("type");
		//alert(std.getParam("type"));
		var act = (type == "new") ? "new" : "list";
		var pam = {"act":act,"index":index};
		

		$.get("/weixin/api.php",pam,function(json){
			if(!json.data.length) {
				$self.html('<a href="javascript:void(0);">没有更多了</a>');
				return false;
			}
			var html = buildHtml(json.data);
			$("#list_box").append(html);
			$self.html('<a href="javascript:void(0);">点击加载更多</a>');
			$self.attr("Available",0);
		});
	});

	var result = function(json){
		console.log(json);
		is_login = json.is_login;

		var html = buildHtml(json.data);
		$("#list_box").html(html);
		$("#js_main").show();
		
	};

	
	//顶部切换按钮
	$("#js_main_type a").on("click",function(e){
		$("#js_main_type li").removeClass("current");
		$(this).parent().addClass("current");
		$("#js_more a").text("点击加载更多");
		$("#js_more a").attr("Available",0);
		
		var act = $(this).attr("href").replace(/.*type=([a-z])/,"$1");
		if($.inArray(act,['all','new']) == -1) {
			act = "all";
		}
		
		var pkg = std.getParam("pkg");
		//alert(pkg);
		if(pkg) {
			$("#js_more").hide();
		} else {
			window.location.href = "index_new.html";
			return false;
			// $("#js_more").show();
		}

		
		$.get("/weixin/api.php",{"act":act,"pkg":pkg},result);
	});
	
	var act = window.location.href.replace(/.*type=([a-z])/,"$1");
	
	if($.inArray(act,['my','new','all']) == -1) {
		act = 'all';
	}
	$("#js_main_type a[act="+ act +"]").click();

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
		if(self.attr("gift_key") != "") {
			showCode(self.attr("gift_key"));
			
			return false;
		} else if($.inArray(self.text(),["立即领取","使用"]) == -1) {
			//alert("错误");
			return false;
		}
		

		var gid = self.attr("gid");
		if(!is_login){
			std.goLogin();
			return false;
		}
		
		$.get("/weixin/api.php",{"act":"get","id":gid},function(json){
			var data = json.data;
			if(data["IS_SUCESSFUL"] != 1) {
				alert("领取失败");
				return false;
			}
			console.log(json);
			self.attr("gift_key",data['GAME_KEY'])
			self.text("使用");
			self.click();
		});
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







