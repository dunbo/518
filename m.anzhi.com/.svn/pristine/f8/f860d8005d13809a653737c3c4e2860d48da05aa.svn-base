var submit_info_clickable = true;
function showOpenBox(obj){
	var bg_h=$(window).height() + $(document).scrollTop(),
				top_h= $(obj).height()/ 2 - $(document).scrollTop();
	$(obj).css("margin-top",-top_h+"px").show();
	$("#body-bg").css("height",bg_h+"px").show();
	flags.canMove=false;
	window.onresize = function(){
		var bg_h=$(window).height() + $(document).scrollTop(),
			 top_h= $(obj).height()/ 2 - $(document).scrollTop();
		$('#body-bg').css("height",bg_h+"px");
		$(obj).css("margin-top",-top_h+"px");	
	}
}
function showOpenBox2(obj){
	var bg_h=$(window).height(),
			top_h= $(obj).height()/ 2;
	$(obj).css("margin-top",-top_h+"px").show();
	$("#body-bg").css("height",bg_h+"px").show();
	flags.canMove=false;
	window.onresize = function(){
		var bg_h=$(window).height();
		$("#body-bg").css("height",bg_h+"px");
	}
}
function cloBox(obj){
	$(obj).hide();
	$("#body-bg").hide();
	flags.canMove=true;
}

// 提交信息
function submit_info() {
	if (!submit_info_clickable) {
		return;
	}
	submit_info_clickable = false;
	$("#prompt").text('');
	var name = $.trim($("#name").val());
	var telephone = $.trim($("#telephone").val());
	var address = $.trim($("#address").val());
	var award_id = $.trim($("#award_id").text());
	if (name == '') {
		submit_info_clickable = true;
		$("#prompt").text('姓名不能为空');
		return;
	}
	if (telephone == '') {
		submit_info_clickable = true;
		$("#prompt").text('请输入手机号');
		return;
	}
	if (address == '') {
		submit_info_clickable = true;
		$("#prompt").text('地址不能为空');
		return;
	}
	var pattern = /^1[34578][0-9]{9}$/
	if (!pattern.test(telephone)) {
		submit_info_clickable = true;
		$("#prompt").text('请输入正确的手机号！');
		return;
	}
	$.ajax({
		url:"/lottery/March_pin_2017/get_info.php?aid="+aid+"&sid="+sid,
		data:{name:name,telephone:telephone,address:address,award_id:award_id},
		type:"post",
		success:function(data){
			if (data == -1) {
				submit_info_clickable = true;
				$("#prompt").text('请插入sim卡！');
			} else if (data == 500) {
				submit_info_clickable = true;
				$("#prompt").text('姓名不能为空！');
			} else if (data == 501) {
				submit_info_clickable = true;
				$("#prompt").text('请输入手机号！');
			} else if (data == 502) {
				submit_info_clickable = true;
				$("#prompt").text('姓名太长！');
			} else if (data == 503) {
				submit_info_clickable = true;
				$("#prompt").text('请输入正确的手机号！');
			} else if (data == 504) {
				submit_info_clickable = true;
				$("#prompt").text('地址不能为空！');
			} else if (data == 200) {
				$("#prompt").text('提交成功！');
				setTimeout(function(){
					submit_info_clickable = true;
					$("#prompt").text('');
					cloBox('#tip-box3');
					window.location.reload();
					//location.href="/lottery/March_pin_2017/my_award.php?aid="+aid+"&sid="+sid+ "&r=" + r + "&#"+archor;
				}, 1000);
			} else {
				submit_info_clickable = true;
				$("#prompt").text('出错啦！');
			}
		}
	});
}
//分享地址为活动页 用的方法
function invite_callback() {
	if (typeof(arguments[0]) != 'undefined') {
		var magic = arguments[0];
		var version = parseInt(arguments[1]);
		var firmware = arguments[2];
		var flag = arguments[3];
		if(version >= 6200){
			js_param = {type:'action', id:aid, callback:null};
			Azfd.lock = false;
			//share_download(js_param, php_param);			
		}else {
			setTimeout(function(){
				var php_url = '/fast.php?';
				for (var i in php_param) {
					php_url += '&' + i + '=' + php_param[i];
				}
				window.location.href=php_url;			
			},1000);		
		} 
	}else{
		setTimeout(function(){
			var php_url = '/fast.php?';
			for (var i in php_param) {
				php_url += '&' + i + '=' + php_param[i];
			}
			window.location.href=php_url;			
		},1000);		
	}
}


function is_null(){}

// 客户端分享回调函数
function onSharedResult(share_result) {
        var result_json = $.parseJSON(share_result);
        //分享应用类型（0：短信，1：新浪微博，2：QQ空间，3：微信好友，4：微信朋友圈，6：QQ好友）
        var appType = result_json.appType; 
         //分享结果（1：分享成功，2：分享取消，3：分享失败）
        var resultType = result_json.resultType;
        // 记日志
        $.ajax({
                url:'/lottery/coactivity_share_result.php',
                data:"aid="+aid+"&sid="+sid+"&appType="+appType+"&resultType="+resultType,
                type:'post',
                success:function(){
                }
        });
}