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
				'<h4>${title}</h4>'+
				'<p">${intro}</p>'+
				'<p>浏览：${number} | ${end_date}</p>'+
				'<a class="click_area" href="${detail}"></a>'+
		'</li>';
		var html = "";
		for(var i=0;i < json.length; ++i) {
			//测试时如果没有这个字段手动加上
			html += std.setTpl(tmp,
				{
					"sid":json[i][0],
					"title":json[i][1],
					"detail":json[i][2],
					"intro":json[i][3],
					"end_date":json[i][5],
					"number":json[i][4],
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
		var title =$("#title").val();
		if(title){
			var act = 'query';
			var pam = {"act":act,"title":title,"index":index};
		}else{
			var act = "all";
			var pam = {"act":act,"index":index};
		}
		$.get("/weixin/strategy.php",pam,function(json){
			var Length = 0;
		    for (var item in json.data) {
		      Length++;
		    }
			if(Length==0) {
				$self.html('<a href="javascript:void(0);">没有更多了</a>');
				return false;
			}
			if(title){
				var html = buildHtml_two(json.data);
			}else{
				var html = buildHtml(json.data);
			}
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
		$("#js_main_two").css('display','none');
		$("#re_search").hide();
		$("#js_main_type li").removeClass("current");
		// $(this).parent().addClass("current");
		$("#js_more a").text("点击加载更多");
		$("#js_more a").attr("Available",0);
		$("#js_more").show();
		$("#title").val("");
		var act = $(this).attr("href").replace(/.*type=([a-z])/,"$1");
		if($.inArray(act,['all']) == -1) {
			act = "all";
		}
		
		$.get("/weixin/strategy.php",{"act":act},result);
	});
	var act = window.location.href.replace(/.*type=([a-z])/,"$1");
	
	if($.inArray(act,['my','new','all']) == -1) {
		act = 'all';
	}
	$("#js_main_type a").click();
	var flags={canMove:true};
	//名称查找
	$("#js_query").on("click",function(){
		var act = 'query';
		var title =$("#title").val();
		if(!title){
			// $('#tip-box1').show();
			// $("#body-bg").show();
			var bg_h=$("#body-bg").height()+$(document).scrollTop(),
			top_h= $("#tip-box1").height()/ 2-$(document).scrollTop();
			$("#tip-box1").css("margin-top",-top_h+"px").show();
			$("#body-bg").css("height",bg_h+"px").show();
			flags.canMove=false;
		}else{
			window.location.href ="strategy_query.html#title="+title;
		}
	});
	$("#js_close_alert").on("click",function() {
		$("#tip-box1").hide();
        $("#body-bg").hide();
        flags.canMove=true;
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







