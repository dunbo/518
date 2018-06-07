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
	if (name == '') {
		submit_info_clickable = true;
		$("#prompt").text('姓名不能为空');
		return;
	}
	if (telephone == '') {
		submit_info_clickable = true;
		$("#prompt").text('手机号码不能为空');
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
		$("#prompt").text('手机号码格式不对！');
		return;
	}
	$.ajax({
		url:"/lottery/long_holiday_2016/get_info.php?aid="+aid+"&sid="+sid,
		data:{name:name,telephone:telephone,address:address},
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
				$("#prompt").text('手机号码不能为空！');
			} else if (data == 502) {
				submit_info_clickable = true;
				$("#prompt").text('姓名太长！');
			} else if (data == 503) {
				submit_info_clickable = true;
				$("#prompt").text('手机号码格式不对！');
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
					//location.href="/lottery/long_holiday_2016/my_award.php?aid="+aid+"&sid="+sid+ "&r=" + r + "&#"+archor;
				}, 1000);
			} else {
				submit_info_clickable = true;
				$("#prompt").text('出错啦！');
			}
		}
	});
}