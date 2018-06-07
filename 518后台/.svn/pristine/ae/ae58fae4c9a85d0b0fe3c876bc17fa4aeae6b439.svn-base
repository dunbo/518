function choose_task_show_bak(val)
{ 
	if(val==1||val==undefined) //页面还没加载红包内容 
	{
		//无任务
		$('tr:[name="get_red_task_conditions"]').attr("style","display:none");
		$("#red_id").removeAttr("readOnly","");
		$("#red_id").attr('onblur',"get_red_detail(this.value,'')");
		var red_id = $('#red_id').val();
		if(red_id)
		{
			get_red_detail(red_id,'');
		}
		//显示市场内页面类型
		$('#cpm_type').css("display","");
		$('#page_content_div').css("display","");
		
		//定制推送  显示推荐内容
		$("#recommend_content_tr").css('display','');
	}
	else if(val==2)
	{
		//有任务
		$("#red_id").attr("readOnly","true");
		$("#red_id").removeAttr('onblur');
		var red_pkg = $('#red_soft_package').val();
		if(red_pkg)
		{
			get_red_detail('',red_pkg);
		}
		$('tr:[name="get_red_task_conditions"]').attr("style","display:");
		
		//显示市场内页面类型
		$('#cpm_type').css("display","none");
		$('#page_content_div').css("display","none");
		
		//定制推送 不显示推荐内容
		$("#recommend_content_tr").css('display','none');
	}
}
function get_red_detail_bak(red_id,red_pkg)
{
	if(red_id)
	{
		//无任务的时候，根据红包id获取红包相关内容
		$("#red_num").val(1111);
		$("#red_total").val(1111);
		$("#red_random_range").val(1111);
		$("#red_grant_type").val(2);
		$("#red_grant_type").attr("disabled","disabled");
	}
	else if(red_pkg)
	{
		//有任务的时候，根据软件获取红包相关内容
		$("#red_id").val(1);
		$("#red_num").val(1111);
		$("#red_total").val(1111);
		$("#red_random_range").val(1111);
		$("#red_grant_type").val(2);
		$("#red_grant_type").attr("disabled","disabled");
		var task_type=2;
		$("#task_type").val(task_type);
		$("#task_type").attr("disabled","disabled");
		var show_str='';
		if(task_type==1)
		{
			show_str="首次下载并打开后，需回来点击领取";
		}
		else if(task_type==2)
		{
			show_str="首次安装并安智账号登录游戏后，需回来点击领取";
		}
		else
		{
			show_str="首次安装体验X分钟后，需回来点击领取";
		}
		$("#red_task_content1").val(show_str);
		$("#red_task_content2").val("请1小时内完成，否则可能被别人抢走");
	}
}