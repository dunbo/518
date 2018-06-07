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
	//顶部切换按钮
	$("#js_main_type a").on("click",function(e){
		$("#js_main_type").css('display','none');
		var act = 'query';
		// var softname =decodeURI(window.location.href).split('=')[1]; 
		var softname =decodeURIComponent(window.location.href).split('&')[0].split('=')[1]; 
		var biaoshi=decodeURIComponent(window.location.href).split('=')[2];
		var pam = {"act":act,"softname":softname,'biaoshi':biaoshi};
		$.get("/weixin/gift.php",pam,function(json){
			console.log(json);
			is_login = json.is_login;
			var softname_base=json.softname_base;
			var softname=json.softname;
			$("#softname").val(softname); 
			if(window.location.href!="http://"+window.location.host+"/weixin/index_query.html#softname="+softname_base+'&biaoshi=1'){
				window.location.href='index_query.html#softname='+softname_base+'&biaoshi=1';
			}
			$("#list_box_two").html("");
			$("#list_search_result").html("");
			var hasProp = false;
			for (var prop in json.data.GAME_DATA){
				hasProp = true;
				break;
			}
			var hasProp_two = false;
			for (var prop in json.data.GIFT_DATA){
				hasProp_two = true;
				break;
			}
			if(!(hasProp_two || hasProp)){
				$("#list_box_two").html('<div class="app_no" id="re_search"><img src="images/app_no.png"/><p>抱歉，搜索无结果</p></div>');
				$("#del").show();
				$("#div_one").css('display',"none");
				$("#div_two").css('display',"none");
				$("#list_search_result").css('display',"none");
				$("#js_main_two").show();
				$("#js_more_two").css('display',"none");
				return;
			}
			$("#del").hide();
			var html = buildHtml(json.data.GAME_DATA,1);
			$("#list_box_two").append(html);
			var html = buildHtml_two(json.data.GIFT_DATA);
			$("#list_search_result").append(html);
			$("#list_search_result li:eq(0)").css('border-top','1px solid #e6e6e6');
			$("#list_search_result li:eq(0)").css('margin-top','10px');
			var index = $("#list_search_result").children().length;			
			if(index==20){
				$("#js_more_two a").html('<a href="javascript:void(0);">点击加载更多</a>');
				$("#js_more_two").show();
			}else{
				$("#js_more_two").css('display','none');
			}
			$("#js_main_two").show();
			$("#div_one").show();
			$("#div_two").show();
			$("#list_search_result").show();
		});
	});
	var act = window.location.href.replace(/.*type=([a-z])/,"$1");
	
	if($.inArray(act,['my','new','all']) == -1) {
		act = 'all';
	}
	$("#js_main_type a[act="+ act +"]").click();
	//名称查找
	$("#js_query").on("click",function(){
		$("#js_main_type").css('display','none');
		var act = 'query';
		var softname =$("#softname").val();
		if(!softname){
			// $('#tip-box1_two').show();
			// $("#body-bg").show();
			var bg_h=$("#body-bg").height()+$(document).scrollTop(),
			top_h= $("#tip-box1").height()/ 2-$(document).scrollTop();
			$("#tip-box1_two").css("margin-top",-top_h+"px").show();
			$("#body-bg").css("height",bg_h+"px").show();
			flags.canMove=false;
		}else{
			if(window.location.href!="http://"+window.location.host+"/weixin/index_query.html#softname="+softname){
				window.location.href = "index_query.html#softname="+softname;
				window.location.reload();
			}
		}
	});

	//加载更多
	$("#js_more_two a").on("click",function(){
		var $self = $(this);

		if($self.attr("cha") == 1) {
			return false;
		}
		$self.attr("cha",1);
		var html = '<div class="app_loading">'+
				'<i>正在加载</i>'+
				'<img src="http://m.anzhi.com/images/loading.gif"/>'+
				'<div class="clear"></div>'+
			'</div>';

		$self.html(html);
		
		
		var index = $("#list_search_result").children().length;
		var act = "query";
		var softname =$("#softname").val();
		var pam = {"act":act,"index":index,"softname":softname};
		

		$.get("/weixin/gift.php",pam,function(json){
			var hasProp = false;
			for (var prop in json.data.GIFT_DATA){
				hasProp = true;
				break;
			}
			if(!hasProp) {
				$self.html('<a href="javascript:void(0);">没有更多了</a>');
				return false;
			}
			var html = buildHtml_two(json.data.GIFT_DATA);
			$("#list_search_result").append(html);
			$self.html('<a href="javascript:void(0);">点击加载更多</a>');
			$self.attr("cha",0);
		});
	});

	function buildHtml_two(json) {
		var tmp = '<li>'+
			'<div class="app_icon">'+
				'<img src="${icon}"/>'+
			'</div>'+
			'<div class="app_info">'+
				'<h4>${name}</h4>'+
				'<p class="app_time">截止时间：${end_date}</p>'+
				'<div class="app_count">'+
					'<p><span style="width:${pt}%"></span></p>'+
					'<em>剩余：<i>${surplus}</i>/${total}</em>'+
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



	$("#list_search_result").on('click',"a[act=get_gift]",function(){
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
			self.attr("gift_key",data['GAME_KEY']);
			self.text("使用");
			self.click();
		});
	});

	$("#js_close_alert").on("click",function() {
		$("#tip-box1").hide();
        $("#body-bg").hide();
        flags.canMove=true;
	});

	$("#js_close_alert_two").on("click",function() {
		$("#tip-box1_two").hide();
        $("#body-bg").hide();
        flags.canMove=true;
	});
	
	$("body").bind('touchmove', function (e) {
		if(!flags.canMove){
			e.preventDefault();
		}
	});
});







