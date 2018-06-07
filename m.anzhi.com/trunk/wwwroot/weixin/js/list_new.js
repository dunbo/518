require.config({
	paths:{
		"jquery" : '//cdn.bootcss.com/jquery/2.1.4/jquery.min',
		"std":"/weixin/js/std"
	}
});

require(['jquery','std'],function($,std){
	var is_login = false;
	function buildHtml(json) {
		var num = arguments[1] ? arguments[1] : 0;
		var tmp = '<li>'+
			'<div class="app_icon">'+
				'<img src="${icon}"/>'+
			'</div>'+
			'<div class="app_info">'+
				'<h4>${name}</h4>'+
				'<p class="app_count2">今日新增：${surplus}个</p>'+
				'<p class="app_count3">礼包总数：${total}个</p>'+
				'<a href="index_pkg.html#pkg=${pkg}" class="click_area"></a>'+
			'</div>'+
		'</li>';
		var html = "";
		if(num==1){
			for(var i=0;i < json.length; ++i) {
				if(i<2){
					html += std.setTpl(tmp,
						{
							"pkg":json[i][1],
							"icon":json[i][3],
							"name":json[i][2],
							"surplus":json[i]['4'],
							"total":json[i]['5'],
						}
					);
				}
			}
		}else{
			for(var i=0;i < json.length; ++i) {
				html += std.setTpl(tmp,
					{
						"pkg":json[i][1],
						"icon":json[i][3],
						"name":json[i][2],
						"surplus":json[i][4],
						"total":json[i][5],
					}
				);
			}
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
		var act ="all";
		var pam = {"act":act,"index":index};
		

		$.get("/weixin/gift.php",pam,function(json){
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
		// $("#js_main_two").css('display','none');
		$("#js_main_type li").removeClass("current");
		$(this).parent().addClass("current");
		$("#js_more a").text("点击加载更多");
		$("#js_more a").attr("Available",0);
		$("#softname").val("");
		var act = $(this).attr("href").replace(/.*type=([a-z])/,"$1");
		if($.inArray(act,['all','new']) == -1) {
			act = "all";
		}
		$("#js_more").show();
		$.get("/weixin/gift.php",{"act":act},result);
	});
	var act = window.location.href.replace(/.*type=([a-z])/,"$1");
	if($.inArray(act,['all']) == -1) {
		act = 'all';
	}
	$("#js_main_type a[act="+ act +"]").click();
	//名称查找
	$("#js_query").on("click",function(){
		var act = 'query';
		var softname =$("#softname").val();
		if(!softname){
			// alert("请输入礼包名称或游戏名称");
			// $('#tip-box1').show();
			// $("#body-bg").show();
			var bg_h=$("#body-bg").height()+$(document).scrollTop(),
			top_h= $("#tip-box1").height()/ 2-$(document).scrollTop();
			$("#tip-box1").css("margin-top",-top_h+"px").show();
			$("#body-bg").css("height",bg_h+"px").show();
			flags.canMove=false;
		}else{
			window.location.href = "index_query.html#softname="+softname;
		}
	});
	var flags={canMove:true};

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
	$(function(){
	    $(".search input").bind('focus', function (e) {
	        $('.search').addClass('search_bg_no');
	    });
		$(".search input").bind('blur', function (e) {
	        $('.search').removeClass('search_bg_no');
	    });
	})
});







