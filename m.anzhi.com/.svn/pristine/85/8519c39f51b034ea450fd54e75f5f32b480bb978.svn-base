// 软件
var soft_lists = [];//返回的全部软件
var soft_lists_go = [];//位置会变化的软件
var stable_soft_lists_go = [];//位置不变化的软件
var stable_soft_lists_order = [];//位置不变化的且排好序的软件

var soft_start = 0, slice_size = 8;
$(function() {
	my_soft();
});

function my_soft() {
	var go1=[], go2=[];
	var json_data = window.AnzhiActivitys.getAllAppList(parseInt(aid));
	var soft_list = $.parseJSON(json_data);
	soft_lists = soft_list.DATA;
	// 排序
	for(var j=0;j<soft_lists.length;j++){
		window.AnzhiActivitys.registerDownloadObserver(parseInt(soft_lists[j][0]));
		window.AnzhiActivitys.registerInstallObserver(soft_lists[j][7]);
		var  soft_status_gos = window.AnzhiActivitys.isInstalledApp(soft_lists[j][7],parseInt(soft_lists[j][13]));
		var json_datas_gos = window.AnzhiActivitys.getDownloadStateForActivity(parseInt(soft_lists[j][0]));
		if ($.inArray(soft_lists[j][7], stable_soft_lists) >= 0) {
			stable_soft_lists_go.push(soft_lists[j]);
		} else if((soft_status_gos == -2 && json_datas_gos== -1) || json_datas_gos == 1 || json_datas_gos == 2 || json_datas_gos == 3) {
			soft_lists_go.push(soft_lists[j]);
		} else if(json_datas_gos == 5 && soft_status_gos == -2) {
			go1.push(soft_lists[j]);
		} else if(soft_status_gos != -2) {
			go2.push(soft_lists[j]);
		}
	}
	// 合并soft_lists_go、go1、go2
	soft_lists_go = soft_lists_go.concat(go1);
	soft_lists_go = soft_lists_go.concat(go2);
	// 对stable_soft_lists_go重新排一下序
	for (var j = 0; j < stable_soft_lists.length; j++) {
		 for (var i = 0; i < stable_soft_lists_go.length; i++){
			if (stable_soft_lists_go[i][7] == stable_soft_lists[j]) {
				stable_soft_lists_order.push(stable_soft_lists_go[i]);
			}
		}
	}
	slice_size = 8 - stable_soft_lists_order.length;
	get_soft();
}

function get_soft() {
	var data = soft_lists_go.slice(soft_start*slice_size, (soft_start+1)*slice_size);
	if (data.length <= 0) {
		soft_start = 0;
		data = soft_lists_go.slice(soft_start*slice_size, (soft_start+1)*slice_size);
	}
	data = stable_soft_lists_order.concat(data);
	soft_start++;
	var str = '';
	for (var i = 0; i < data.length; i++) {
		var soft = data[i];
		var softname = soft[2];
		var short_softname;
		short_softname = softname;
		var softid = parseInt(soft[0]);
		var json_datas = window.AnzhiActivitys.getDownloadStateForActivity(softid);
		var soft_status = parseInt(json_datas);
		// 根据软件不同状态，拼接展示
		var my_soft = '';
		var pkgname = soft[7];
		var versionCode = parseInt(soft[13]);
		var size = soft[27];
		var icon_url = soft[1];
		if (soft_status == 1) {
			my_soft = '<a id="'+softid+'" onclick="download_apk('+aid+','+ softid +',\''+ pkgname +'\',\''+ softname +'\','+ versionCode +','+ size + ',0);" class="downlode">下载中</a>';
		} else if (soft_status == 2 || soft_status == 3 || soft_status == 8) {
			my_soft = '<a id="'+softid+'" onclick="download_apk('+aid+','+ softid +',\''+ pkgname +'\',\''+ softname +'\','+ versionCode +','+ size +',0);" class="downlode">继续</a>';
		} else if (soft_status == 4) {
			my_soft = '<a id="'+softid+'" onclick="download_apk('+aid+','+ softid +',\''+ pkgname +'\',\''+ softname +'\','+ versionCode +','+ size +',0);" class="downlode">下载</a>';
		} else if (soft_status == 5) {
			my_soft = '<a id="'+soft[0]+'" onclick="installApp('+ softid +');" class="downlode">安装</a>';
		} else if (soft_status == 6) {
			my_soft = '<a id="'+soft[0]+'" class="downlode" onclick="openApp(\''+ pkgname +'\','+softid+');">打开</a>';
		} else if (soft_status == 9) {
			my_soft = '<a value="校验中" id="'+ softid +'" class="downlode">校验中</a>';
		} else if (soft_status == 10) {
			my_soft = '<a id="'+ softid +'" class="downlode">已下载</a>';
		} else if (soft_status == -1) {
			var soft_other_status = window.AnzhiActivitys.isInstalledApp(pkgname, versionCode);
			if (soft_other_status == -2) {
				my_soft = '<a id="'+softid+'" onclick="download_apk('+aid+','+ softid +',\''+ pkgname +'\',\''+ softname +'\','+ versionCode +','+ size +',0);" class="downlode">下载</a>';
			} else if (soft_other_status == -1) {
				my_soft = '<a id="'+softid+'" onclick="download_apk('+aid+','+ softid +',\''+ pkgname +'\',\''+ softname +'\','+ versionCode +','+ size +',0);" class="downlode">更新</a>';
			} else if (soft_other_status == 0) {
				my_soft = '<a id="'+soft[0]+'" class="downlode" onclick="openApp(\''+ pkgname +'\','+softid+');">打开</a>';
			} else if (soft_other_status == 1) {
				my_soft = '<a id="'+soft[0]+'" class="downlode">已安装</a>';
			}
		}
		
		str += '<li><p class="soft-icon"><a href="javascript:;"><img src="' + icon_url + '"/></a></p><div class="soft-txt"><p class="soft-name"><a href="javascript:;">' + short_softname + '</a></p><p>' + my_soft + '</p></div></li>';
		
	}
	$('#my_softs').html(str);
}

function download_apk(aid, softid, pkgname, softname, versionCode, size, flag) {
	window.AnzhiActivitys.downloadForActivity(parseInt(aid),parseInt(softid),pkgname,softname,parseInt(versionCode),size,flag);
	download_go(pkgname);
}

function installApp(softid) {
	window.AnzhiActivitys.installAppForActivity(parseInt(softid));
}

function openApp(pkgname, softid) {
	window.AnzhiActivitys.openAppForActivity(pkgname,[softid]);
}

function onDownloadStateChanged(softid,newState){
	if(newState == 1){
		$('#'+softid+'').html("下载中");
	}else if(newState == 2){
		$('#'+softid+'').html("继续");
	}else if(newState == 3){
		$('#'+softid+'').html("继续");
	}else if(newState == 4){
		$('#'+softid+'').html("重试");
	}else if(newState == 5){
		$('#'+softid+'').html("安装");
		$('#'+softid+'').removeAttr("onclick");
		$('#'+softid+'').bind('click',function(){
			installApp(parseInt(softid));
		});
	}else if(newState == 6){
		$('#'+softid+'').html("打开");
		$('#'+softid+'').removeAttr("onclick");
		for(i=0;i < soft_lists.length;i++){
			if(soft_lists[i][0] == softid){
				$('#'+softid+'').bind('click',function(){
					openApp(soft_lists[i][7],softid);
				});
				break;
			}
		}
	}else if(newState == 8){
		$('#'+softid+'').html("继续");
	}else if(newState == 9){
		$('#'+softid+'').html("检查中");
	}
}

function download_go(pkgname) {
	$.ajax({
		url:'/lottery/christmas2015_lottery_download.php?sid='+sid,
		data:'package='+pkgname,
		type:'post',
		success:function(data){
			setTimeout(function() {
				$("#lottery_num").html(data);
				if (data > 0) {
					if ($(".rotate-pointer").hasClass('pointer-disabled')) {
						$(".rotate-pointer").bind('click', lottery_click);
						$(".rotate-pointer").bind('dblclick', lottery_click);
						$(".rotate-pointer").removeClass('pointer-disabled');
					}
				}
			},5000);
		}
	});
}