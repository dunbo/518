var p=0,soft_lists_go = [];
//var open_pkg_arr = [];
function my_soft(aid,sid,limit,go_from,task_packages,package_arr_task_down) {
        //$(".soft_wrap").hide();
        //$(".soft_txt").hide();
	var json_data = window.AnzhiActivitys.getAllAppList(parseInt(aid));
	var cmd = 'var soft_list=' + json_data;
	eval(cmd);
	var soft_lists = soft_list.DATA;
	var open_list = [];
	var install_list = [];
	var down_list = [];

	for(var j=0;j<soft_lists.length;j++){
                var breakOuter=new Boolean(false);
		window.AnzhiActivitys.registerDownloadObserver(parseInt(soft_lists[j][0]));
		window.AnzhiActivitys.registerInstallObserver(soft_lists[j][7]);
		var json_data =  window.AnzhiActivitys.isInstalledApp(soft_lists[j][7],parseInt(soft_lists[j][13]));
		var soft_status_gos = window.AnzhiActivitys.getDownloadStateForActivity(parseInt(soft_lists[j][0]));
                if(json_data == 1 && soft_status_gos == -1){//已下载状态过滤
			continue;
		}

		if(json_data == 0 || json_data == 1 ){
	            for(var s=0;s<task_packages.length;s++){
                        if(task_packages[s]==soft_lists[j][7]){
                            open_list.push(soft_lists[j]);
                            //open_pkg_arr.push(soft_lists[j][7]);
                            break;
                        }
                    }
		}
		if(json_data == -2 && soft_status_gos == 5){
	            for(var s=0;s<task_packages.length;s++){
                        if(task_packages[s]==soft_lists[j][7]){
			    install_list.push(soft_lists[j]);
                            break;
                        }
                    }
		}
		if((json_data == -2 && soft_status_gos != 5 ) || json_data == -1){
	            for(var t=0;t<package_arr_task_down.length;t++){
                        if(package_arr_task_down[t]==soft_lists[j][7]){
                            breakOuter = true;
                            break;
                        }
                    }

                    if (breakOuter==true) {
                            continue;
                    }
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
        $(".soft_wrap").show();
        $(".soft_txt").show();
	get_soft(aid,sid,limit,go_from);
}


function get_soft(aid,sid,limit,go_from){
	var pagemax= Math.floor(soft_lists_go.length/limit);

        if(soft_lists_go.length<6&&soft_lists_go.length>3){
            soft_lists_go.length=3;
        }

        if(soft_lists_go.length<3){
            $(".soft_wrap").hide();
            $(".soft_txt").hide();
        }

	if(soft_lists_go.length%limit==0){
		pagemax = pagemax-1;
	}
	var data = soft_lists_go.slice(p*limit,limit*(p+1));
	if(data.length == 0){
		p = 0;
		data = soft_lists_go.slice(p*limit,limit*(p+1));
	}

	var str = '';
	for(i = 0; i < data.length; i++) {
		if(data[i][2].length > 4){
			var softname = data[i][2].substring(0,3)+'...';
		}else{
			var softname = data[i][2];
		}
		var json_datas = window.AnzhiActivitys.getDownloadStateForActivity(parseInt(data[i][0]));
		//var cmd = 'var soft_status=' + json_datas;

                var json_data_gos =  window.AnzhiActivitys.isInstalledApp(data[i][7],parseInt(data[i][13]));
		var cmd = 'var soft_status=' + json_datas;
		eval(cmd);
		var cmd_gos = 'var soft_status_gos=' + json_data_gos;
		eval(cmd_gos); 
		if( soft_status == 6 && soft_status_gos == -2 ) {
			soft_status = 4;
		}

		//eval(cmd);		
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

function get_soft_li_l(aid,sid,soft_status,softid,my_package,softname,versioncode,size,src){
	if(soft_status == 1){
		var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn"><a id="'+softid+'" href="javacript:;" class="download" >下载中</a></p></li>';
	}else if(soft_status == 3 || soft_status == 8){
		var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a id="'+softid+'" href="javacript:;" class="download" >继续</a></p></li>';
	}else if(soft_status == 2 ){
		var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a id="'+softid+'" href="javacript:;" class="download" >等待中</a></p></li>';
	}else if(soft_status == 4){
		var soft_li = '<li id="li_'+softid+'" onclick="go_softinfo_down('+softid+',\''+my_package+'\',\''+softname+'\','+versioncode+','+size+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a id="'+softid+'" href="javacript:;" class="download" >下载</a></p></li>';
	}else if(soft_status == 5){
		var soft_li = '<li id="li_'+softid+'" onclick="installApp('+softid+',\''+my_package+'\','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;" ><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn"><a href="javacript:;" id="'+softid+'" class="download install_btn" >安装</a></p></li>';
	}else if(soft_status == 6){
		var soft_li = '<li id="li_'+softid+'" onclick="openApp(\''+my_package+'\','+softid+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;" ><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a href="javacript:;" class="download open_btn" >打开</a></p></li>';
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
			var soft_li = '<li id="li_'+softid+'" onclick="openApp(\''+my_package+'\','+softid+','+aid+',\''+sid+'\')" ><p class="soft-icon"><a href="javascript:;" ><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn" ><a href="javacript:;" class="download open_btn" >打开</a></p></li>';
		}else if(soft_other_status == 1){
			var soft_li = '<li ><p class="soft-icon"><a href="javascript:;"><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p class="btn"><a id="'+softid+'" href="javacript:;" class="download" >已下载</a></p></li>';
			//var soft_li = '<li onclick="openApp(\''+my_package+'\','+softid+')" ><p class="soft-icon"><a href="javascript:;" ><img src="'+src+'"/></a></p><p class="soft-name">'+softname+'</p><p><a href="javacript:;" class="download" >打开</a></p></li>';
		}
	}
	return soft_li;
}
function openApp(pkg,softid){
	the_soft = [softid];
	$.ajax({
		url: '/lottery/red_package/open_soft.php',
		data: 'softid='+softid+'&sid='+sid+'&package='+pkg+'&aid='+aid,
		type: 'get',
		success: function(data){
                        //刷新
			window.AnzhiActivitys.openAppForActivity(pkg,the_soft);
                        window.location.reload();
		}
	});
}


function go_softinfo_down(softid,my_package,softname,versioncode,size,aid,sid,details){
	$.ajax({
		url: '/lottery/red_package/download_soft.php',
		data:"softid="+softid+"&sid="+sid+"&aid="+aid+"&pkgname="+my_package, 
		type:"post",
		success:function(data) {
			if(details == 1){
				details =1;
			}else{
				details = 0
			}
			window.AnzhiActivitys.downloadForActivity(parseInt(aid),softid,my_package,softname,versioncode,size,details);
		},
	});
}
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
