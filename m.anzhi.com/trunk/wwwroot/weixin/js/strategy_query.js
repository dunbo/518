require.config({
	paths:{
		"jquery" : '//cdn.bootcss.com/jquery/2.1.4/jquery.min',
		"std":"/weixin/js/std"
	}
});

require(['jquery','std'],function($,std){
	var is_login = false;


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
		var title =decodeURIComponent(window.location.href).split('=')[1];
		// var title =std.getParam("title");
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
		$("#js_main").css('display','none');
		var act = 'query';
		var title =decodeURIComponent(window.location.href).split('=')[1];
		$("#title").val(title); 
		var pam = {"act":act,"title":title};
		$.get("/weixin/strategy.php",pam,function(json){
			$("#list_box").html("");
			console.log(json);
			var hasProp = false;
			for (var prop in json.data){
				hasProp = true;
				break;
			}
			if(!hasProp) {
				$("#re_search").show();
				$("#js_main").hide();
				$("#del").show();
				return;
			}else{
				$("#re_search").hide();
				$("#js_main").show();
			}
			$("#del").hide();
			var html = buildHtml_two(json.data);
			$("#list_box").append(html);
			var index = $("#list_box").children().length;
			if(index==20){
				$("#js_more a").html('<a href="javascript:void(0);">点击加载更多</a>');
				$("#js_more").show();
			}else{
				$("#js_more").css('display','none');
			}
			$("#js_main").show();
		});
	});
	$("#js_main_type a").click();
	//名称查找
	var flags={canMove:true};
	$("#js_query").on("click",function(){
		var act = 'query';
		var title =$("#title").val();
		if(!title){
			var bg_h=$("#body-bg").height()+$(document).scrollTop(),
			top_h= $("#tip-box1").height()/ 2-$(document).scrollTop();
			$("#tip-box1").css("margin-top",-top_h+"px").show();
			$("#body-bg").css("height",bg_h+"px").show();
			flags.canMove=false;
		}else{
		$("#js_main").css('display','none');
		window.location.href = "strategy_query.html#title="+title;
		$("#js_main_type a").click();
		}
		
	});
	function buildHtml_two(json) {
		var tmp = '<li>'+
				'<h4>${title}</h4>'+
				'<p>${intro}</p>'+
				'<p>浏览：${number} | ${end_date}</p>'+
				'<a class="click_area" href="${detail}"></a>'+
		'</li>';
		var html = "";
		for(var i in json) {
			html += std.setTpl(tmp,
				{	
					"sid":json[i].id,
					"title":json[i].news_name,
					"detail":"http://118.26.203.23/gamenews_"+json[i].id+".html",
					"intro":json[i].news_content,
					"end_date":std.date('Y-m-d',json[i].release_tm),
					"number":json[i].visit_count+'次',
				}
			);
		}
		return html;
	}
	$("#js_close_alert").on("click",function() {
		$("#tip-box1").hide();
        $("#body-bg").hide();
        flags.canMove=true;
	});
});







