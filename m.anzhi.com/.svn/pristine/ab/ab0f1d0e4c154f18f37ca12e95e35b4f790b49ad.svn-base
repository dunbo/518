var p=0,soft_lists_go = [];
var open_pkg_arr = [];
var uid = 0;
var package_task = [];
var package_task_done = [];

function my_soft(aid,sid,limit,go_from,uuid,package_task_json,package_task_done_json) {
	uid = uuid;
	if( package_task_json !='' ) {
		var pcmd = "var package_task_arr = "+package_task_json;
		eval(pcmd);
		package_task = package_task_arr;
	}
	if( package_task_done_json !='' ) {
		var ppcmd = "var package_task_done_arr = "+package_task_done_json;
		eval(ppcmd);
		package_task_done = package_task_done_arr;
	}
	var json_data = window.AnzhiActivitys.getAllAppList(parseInt(aid));
	var cmd = 'var soft_list=' + json_data;
	eval(cmd);
	var soft_lists = soft_list.DATA;
	
	var open_list = [];
	var install_list = [];
	var down_list = [];
	for(var j=0;j<soft_lists.length;j++){
		window.AnzhiActivitys.registerDownloadObserver(parseInt(soft_lists[j][0]));
		window.AnzhiActivitys.registerInstallObserver(soft_lists[j][7]);
		var json_data =  window.AnzhiActivitys.isInstalledApp(soft_lists[j][7],parseInt(soft_lists[j][13]));
		var soft_status_gos = window.AnzhiActivitys.getDownloadStateForActivity(parseInt(soft_lists[j][0]));
		//已完成软件任务直接过滤
		if( is_package_task(soft_lists[j][7], package_task_done) ) {
			continue;
		}
		//处理过滤已安装
		if( (json_data == 0 || json_data == 1) && !is_package_task(soft_lists[j][7], package_task) ) {
			continue;
		}
		//处理市场外的过滤已安装
		if(json_data == 1 && soft_status_gos == -1) {
			continue;
		}
		if(json_data == 0 || json_data == 1 ){
			open_list.push(soft_lists[j]);
			open_pkg_arr.push(soft_lists[j][7]);
		}
		if(json_data == -2 && soft_status_gos == 5){
			install_list.push(soft_lists[j]);
		}
		if((json_data == -2 && soft_status_gos != 5 ) || json_data == -1){
			down_list.push(soft_lists[j]);
		}
	}      
  	for(var ii in open_list) {
		soft_lists_go.push(open_list[ii]);
	}  
  	for(var ii in install_list) {
		soft_lists_go.push(install_list[ii]);
	}
  	for(var ii in down_list) {
		soft_lists_go.push(down_list[ii]);
	}
	get_soft(aid,sid,limit,go_from);
}


function get_soft(aid,sid,limit,go_from){
	var soft_count = soft_lists_go.length;
	if( soft_count < 3 ) {
		$('#soft_layout').hide();
		return false;
	}else {
		$('#soft_layout').show();
	}
	var pagemax= Math.floor(soft_count/limit);
	if(soft_lists_go.length%limit==0){
		pagemax = pagemax-1;
	}
	var data = soft_lists_go.slice(p*limit,limit*(p+1));
	//规则:翻到最后一页不是6个或3个为一整行的 小于6大于3则只显示一行3个的   小于3个的翻回第一页
	var data_count = data.length;
	if( data_count < 3  ) {
		p = 0;
		data = soft_lists_go.slice(p*limit,limit*(p+1));
	}else if( data_count >3 && data_count < 6 ) {
		data = data.slice(0,3);
	}

	var str = '';
	for(i = 0; i < data.length; i++) {
		if(data[i][2].length > 4){
			var softname = data[i][2].substring(0,3)+'...';
		}else{
			var softname = data[i][2];
		}
		var json_datas = window.AnzhiActivitys.getDownloadStateForActivity(parseInt(data[i][0]));
		var json_data_gos =  window.AnzhiActivitys.isInstalledApp(data[i][7],parseInt(data[i][13]));
		var cmd = 'var soft_status=' + json_datas;
		eval(cmd);
		var cmd_gos = 'var soft_status_gos=' + json_data_gos;
		eval(cmd_gos); 
		if( soft_status == 6 && soft_status_gos == -2 ) {
			soft_status = 4;
		}
		if(data[i][2].length > 4){
			var softname = data[i][2].substring(0,3)+'...';
		}else{
			var softname = data[i][2];
		}	
		if(go_from == 1){
			//排行榜（无软件名称、状态按钮）
			str += get_soft_li(aid,sid,soft_status,parseInt(data[i][0]),data[i][7],softname,parseInt(data[i][13]),parseInt(data[i][27]),data[i][1]);
		}else{
			//抽奖页面（有软件名称、和状态按钮）
			str += get_soft_li_l(aid,sid,soft_status,parseInt(data[i][0]),data[i][7],softname,parseInt(data[i][13]),parseInt(data[i][27]),data[i][1]);
		}
	}
	
	$('#soft-list').html(str);

	if(p==pagemax){
		p=0;
	}else{
		p=p+1;
	}
}
//排行榜（无软件名称、状态按钮）
function get_soft_li(aid,sid,soft_status,softid,my_package,softname,versioncode,size,src){
	if(soft_status == 1){
		var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><img src="'+src+'"/></p><p class="soft-name">'+softname+'</p></li>';
	}else if(soft_status == 2 || soft_status == 3 || soft_status == 8){
		var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><img src="'+src+'"/></p><p class="soft-name">'+softname+'</p></li>';
	}else if(soft_status == 4){
		var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><img src="'+src+'"/></p><p class="soft-name">'+softname+'</p></li>';
	}else if(soft_status == 5){
		var soft_li = '<li id="li_'+softid+'" onclick="installApp('+softid+',\''+my_package+'\','+aid+',\''+sid+'\')" ><p class="soft-icon"><img src="'+src+'"/></p><p class="soft-name">'+softname+'</p></li>';
	}else if(soft_status == 6){
		var soft_li = '<li id="li_'+softid+'" onclick="openApp(\''+my_package+'\','+softid+','+aid+',\''+sid+'\')" ><p class="soft-icon"><img src="'+src+'"/></p><p class="soft-name">'+softname+'</p></li>';
	}else if(soft_status == 9){
		var soft_li = '<li id="li_'+softid+'" onclick="installApp('+softid+',\''+my_package+'\','+aid+',\''+sid+'\')" ><p class="soft-icon<img src="'+src+'"/></p><p class="soft-name">'+softname+'</p></li>';
	}else if(soft_status == 10){
		var soft_li = '<li><p class="soft-icon"><img src="'+src+'"/></p><p class="soft-name">'+softname+'</p></li>';
	}else if(soft_status == -1){
		var soft_other_status = window.AnzhiActivitys.isInstalledApp(my_package,parseInt(versioncode));
		if(soft_other_status == -2){
			var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><img src="'+src+'"/></p><p class="soft-name">'+softname+'</p></li>';
		}else if(soft_other_status == -1){
			var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><img src="'+src+'"/></p><p class="soft-name">'+softname+'</p></li>';
		}else if(soft_other_status == 0){
			//过滤已安装的软件  带任务的软件不过滤
			if( is_package_task( my_package, package_task ) ) {
				var soft_li = '<li id="li_'+softid+'" onclick="openApp(\''+my_package+'\','+softid+','+aid+',\''+sid+'\')" ><p class="soft-icon"><img src="'+src+'"/></p><p class="soft-name">'+softname+'</p></li>';
			}else {
				var soft_li = '';
			}
		}else if(soft_other_status == 1){
			var soft_li = '<li ><p class="soft-icon"><img src="'+src+'"/></p><p class="soft-name">'+softname+'</p></li>';
		}
	}
	return soft_li;
}
function get_soft_li_l(aid,sid,soft_status,softid,my_package,softname,versioncode,size,src){
	if(soft_status == 1){
		var soft_li = '<li id="li_'+softid+'" ><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn"><a href="javacript:;" class="download" >下载中</a></p></li>';
	}else if(soft_status == 3 || soft_status == 8){
		var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a id="'+softid+'" href="javacript:;" class="download" >继续</a></p></li>';
	}else if( soft_status == 2 ) {
		var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a id="'+softid+'" href="javacript:;" class="download" >等待中</a></p></li>';
	}else if(soft_status == 4){
		var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a id="'+softid+'" href="javacript:;" class="download" >下载</a></p></li>';
	}else if(soft_status == 5){
		var soft_li = '<li id="li_'+softid+'" onclick="installApp('+softid+',\''+my_package+'\','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;" ><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn"><a href="javacript:;" id="'+softid+'" class="download" >安装</a></p></li>';
	}else if(soft_status == 6){
		var soft_li = '<li id="li_'+softid+'" onclick="openApp(\''+my_package+'\','+softid+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;" ><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a href="javacript:;" class="download" >打开</a></p></li>';
	}else if(soft_status == 9){
		var soft_li = '<li id="li_'+softid+'" onclick="installApp('+softid+',\''+my_package+'\','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;" ><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a href="javacript:;" class="download" >校验中</a></p></li>';
	}else if(soft_status == 10){
		var soft_li = '<li><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a id="'+softid+'" href="javacript:;" class="download" >已下载</a></p></li>';
	}else if(soft_status == -1){
		var soft_other_status = window.AnzhiActivitys.isInstalledApp(my_package,parseInt(versioncode));
		if(soft_other_status == -2){
			var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a id="'+softid+'" href="javacript:;" class="download" >下载</a></p></li>';
		}else if(soft_other_status == -1){
			var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a id="'+softid+'" href="javacript:;" class="download" >更新</a></p></li>';
		}else if(soft_other_status == 0){
			//过滤已安装的软件  带任务的软件不过滤
			if( is_package_task( my_package, package_task ) ) {
				var soft_li = '<li id="li_'+softid+'" onclick="openApp(\''+my_package+'\','+softid+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;" ><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a href="javacript:;" class="download" >打开</a></p></li>';
			}else {
				var soft_li = '';
			}
		}else if(soft_other_status == 1){
			var soft_li = '<li ><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn"><a id="'+softid+'" href="javacript:;" class="download" >已下载</a></p></li>';
		}
	}
	return soft_li;
}

function is_package_task( search, arr){
	for(var i=0,k=arr.length;i<k;i++){ 
		if( search == arr[i]){ 
			return true; 
		}
	}
	return false;
}


//打开软件
function openApp(pkg,softid,aid,sid){
	if( uid=="" ) {
		login(login_url,version_code);
		return false;
	}
	the_soft = [softid];
	$.ajax({
		url: '/lottery/vacation_gift_open.php',
		data: 'softid='+softid+'&sid='+sid+'&package='+pkg+'&aid='+aid,
		type: 'get',
		success: function(data){
			window.AnzhiActivitys.openAppForActivity(pkg,the_soft);
			//打开软件送抽奖机会
			soft_task_open(pkg,sid,aid);
		}
	});
}

//下载软件
function go_softinfo_down(softid,my_package,softname,versioncode,size,aid,sid,details){
	if( uid=="" ) {
		login(login_url,version_code);
		return false;
	}
	if($('#'+softid).text()=='下载'){
		$('#'+softid).text("等待中");
	}
	$.ajax({
		url: '/lottery/year_feedback_download.php',  //supwater  点击详情 点击下载
		data:"softid="+softid+"&sid="+sid+"&aid="+aid+"&pkgname="+my_package, 
		type:"post",
		success:function(data) {
			if(details == 1){
				details =1;
			}else{
				details = 0
			}
			window.AnzhiActivitys.downloadForActivity(parseInt(aid),softid,my_package,softname,versioncode,size,details);
			//加入任务
			soft_task_add(my_package,sid,aid);
		},
	});
}

//安装软件
function installApp(softid,pkgname,aid,sid){
	$.ajax({
		url: '/lottery/vacation_third_install.php',
		data: 'softid='+softid+'&sid='+sid+'&package='+pkgname+'&aid='+aid,
		type: 'post',
		success: function(data){
			window.AnzhiActivitys.installAppForActivity(softid);
		}
	});
}
function onDownloadStateChanged(softid,newState){
	var newState = parseInt(newState);
	var softid = parseInt(softid);
	if(newState == 1){
		$('#'+softid).text("下载中");
		$('#'+softid).removeAttr("onclick");
		for(i=0;i < soft_lists_go.length;i++){
			if(soft_lists_go[i][0] == softid){
				$('#'+softid).attr('onclick',"go_softinfo_down("+parseInt(softid)+",'"+soft_lists_go[i][7]+"','"+soft_lists_go[i][2]+"',"+soft_lists_go[i][13]+","+soft_lists_go[i][27]+","+parseInt(aid)+",'"+sid+"')");
			}
		}
	}else if(newState == 2){
		$('#'+softid).text("继续");
	}else if(newState == 3){
		$('#'+softid).text("继续");
	}else if(newState == 4){
		$('#'+softid).text("重试");
	}else if(newState == 5){
		$('#'+softid).text("安装").removeAttr("onclick");
		$('#li_'+softid).removeAttr("onclick");
		for(var i=0;i < soft_lists_go.length;i++){
			if(soft_lists_go[i][0] == softid){
				$('#li_'+softid).attr('onclick',"installApp("+parseInt(softid)+",'"+soft_lists_go[i][7]+"')");
                break;
			}
		}
	}else if(newState == 6){
		$('#'+softid).text("打开").removeAttr("onclick");
		for(var i=0;i < soft_lists_go.length;i++){		
			if(soft_lists_go[i][0] == softid){
				$('#li_'+softid).attr('onclick',"openApp('"+soft_lists_go[i][7]+"',"+parseInt(softid)+")");
                break;
			}
		}

	}else if(newState == 8){
		$('#'+softid).text("继续");
	}else if(newState == 9){
		$('#'+softid).text("检查中");
	}
}
function onDownloadDeleted(softid)
{
	location.reload();
}
function is_soft_status(pkg,softid,versioncode,softname,size,aid,sid){
	var json_data =  window.AnzhiActivitys.isInstalledApp(pkg,versioncode);
	var soft_status_gos = window.AnzhiActivitys.getDownloadStateForActivity(parseInt(softid));
	if(json_data == 0 || json_data == 1 ){
		openApp(pkg,parseInt(softid),aid,sid);
	}
	if(json_data == -2 && soft_status_gos == 5){
		installApp(parseInt(softid),pkg,aid,sid);
	}
	if((json_data == -2 && soft_status_gos != 5 ) || json_data == -1){
		go_softinfo_down(parseInt(softid),pkg,softname,parseInt(versioncode),parseInt(size),aid,sid,1);
	}
}

function change_soft(aid,sid,limit,go_from){
	$('#soft-list').html('');
	get_soft(aid,sid,limit,go_from);
}

//复制去使用的情况调此方法
function go_gift_do(pkg,sid,aid){
	$.ajax({
		url: '/lottery/nov_sign/userinfo.php',
		data:"pkg="+pkg+'&sid='+sid+'&aid='+aid,
		type:"post",
		dataType: 'json',
		success:function(data) {
			var softname = data.softname;
			var softid = data.softid;
			var versioncode = data.version_code;
			var size = data.size;
			is_soft_status(pkg,softid,versioncode,softname,parseInt(size),aid,sid);
		},
	});	
}

//加入任务包
function soft_task_add(pkg,sid,aid)
{
	if( uid=="" ) {
		login(login_url,version_code);
		return false;
	}
	$.ajax({
		url: '/lottery/red_ffl/soft_task.php',
		data:'type='+1+"&pkg="+pkg+'&sid='+sid+'&aid='+aid+'&uid='+uid,
		type:"post",
		dataType: 'json',
		success:function(data) {
			//alert(data.msg);
		},  			
	});	
}

//打开软件
function soft_task_open(pkg,sid)
{
	if( uid=="" ) {
		login(login_url,version_code);
		return false;
	}
	$.ajax({
		url: '/lottery/red_ffl/soft_task.php',
		data:'type='+2+"&pkg="+pkg+'&sid='+sid+'&aid='+aid+'&uid='+uid,
		type:"post",
		dataType: 'json',
		success:function(data) {
			if( data.code == 1 ) {
				$('#lottery_add').text(1);
				lottery_num = lottery_num + 1;
				$('#lottery_num').text(lottery_num);
				window.AnzhiActivitys.showToastForJs('恭喜您在本局中获得一次抽奖机会');
			}else {
				//alert(data.msg)
			}
			location.reload();
		},
	});	
}