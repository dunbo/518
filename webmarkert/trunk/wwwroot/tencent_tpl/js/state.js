
$.extend({
        invokeExt: function (name) {
                if (typeof name == "string") {
                        var ret = undefined;
                        try {
                                if (arguments.length > 1) {
                                        ret = window.external.Molo.Invoke(arguments);
                                } else {
                                        ret = window.external.Molo.Invoke(name);
                                }
                        } catch (ex) {
                                return false;
                        }
                        if (typeof ret != undefined) {
                                return ret;
                        }
                } 
                else {
                        return {};
                } 
        }
}); 
var list = new Array();
var userAppHash; 
var	userUpgradeHash = new Object();
//通用
var installing  = "<img src='/tencent_tpl/images/install_small02.png'>";
var downloading = "<img src='/tencent_tpl/images/install_small03.png'>";
var installed   = "<img src='/tencent_tpl/images/install_small05.png'>";
var upgrade     = "<img src='/tencent_tpl/images/install_small04.png'>";
var install     = "<img src='/tencent_tpl/images/install_small01.png'>";
//详细页
var de_installing  = "<img src='/tencent_tpl/images/install_btn02.png'>";
var de_downloading = "<img src='/tencent_tpl/images/install_btn03.png'>";
var de_installed   = "<img src='/tencent_tpl/images/install_btn05.png'>";
var de_upgrade     = "<img src='/tencent_tpl/images/install_btn04.png'>";
var de_install     = "<img src='/tencent_tpl/images/install_btn01.png'>";
$(document).ready(function(){ 
   $.invokeExt("DomReady","appUpdate");
});

/**
  * desc 获取pc端返回JSON数据并解析,数据格式如{code:'init',list:[{n:'com.cola.twisohu',c:'12'}]}
  * init 当前软件安装状态
  * list 软件列表
  * n    软件包名
  * c    软件版本号
  */
var appUpdateCallback = function(app) {
    var listObj = eval("("+app+")");
	var code = listObj.code ? listObj.code : 'init';
	list = listObj.list;
	var x;
	var i = 0;
	userAppHash = new Object();
	for(x in list){
		//以包名作为key,userAppHash用于存储腾讯手机管家返回的数据
		var pkg = list[x].n;
		userAppHash[pkg] = list[x];
		i++;
	}
	if(!appList) return false;
	if(code == 'init' && i == 0){
		for(x in appList) {
			var app = appList[x];
			setInitStatus(app.id,code);
		}
	}else{
		for(x in appList) {
			var app = appList[x];
			var pkg = app.package;
			var userAppInfo = userAppHash[pkg];
			if(!userAppInfo) continue;
			setSoftInstallStateInfo(app.id,app.package,app.versionCode,code);
		}
	}
}

/**
  * desc 设置软件的安装状态
  * softid web端软件编号
  * pkg web端软件包名
  * vc web端软件版本号
  * pccode pc端软件安装状态
  */

function setSoftInstallStateInfo(softid,pkg,vc,pccode){
	var userAppInfo = userAppHash[pkg];
	var state       = getSoftInstallState(pccode);
	pc = parseInt(userAppInfo.c); //手机端软件versioncode
	wc = parseInt(vc); //web端软件versioncode
	//下载过程中 中断下载 的处理
	if(pc > 0 && pccode != 'install'){
		if(wc <= pc){
		   state = (pccode == 'del') ? 1 : 4;
		}else{
		  state = 5;
		}
	}else if((pccode == 'downed' || pccode == 'installed') && userUpgradeHash[pkg]){
		state = 5;
	}

	switch(state){
		case 1: 
				display_stat(softid,state,false);
				break;
		case 2: 
				display_stat(softid,state,true);
				break;
		case 3:
				display_stat(softid,state,true);		
				break;		
		case 4: 
				display_stat(softid,state,true);
				break;
		case 5:	
				userUpgradeHash[pkg] = 	userAppInfo;	
				display_stat(softid,state,false);
				break;
		default:
				display_stat(softid,1,false);		
	}
}

/**
  * desc 获取软件的安装状态
  * code pc端软件安装实时状态
  */
function getSoftInstallState(code) {
	if('init'==code||'del'==code){      //初始化或者删除时显示安装
	  return 0;
	}else if('installed'==code||'downed'==code){   
	  //安装或下载完成 将下载或安装完成的apk的安装状态按钮返回到之前未安装或升级状态
	  return 1;
	}else if('install'==code){//正在安装
	  return 2;
	}else if('down'==code){//正在下载
	  return 3;
	}else if('add'==code||'update'==code){ //安装完成如果版本低的显示升级,版本高的显示已安装
	  return 4;
	}else {
	  return 0;
	}
}

function setInitStatus(softid,code){
	display_stat(softid,1,false)
}

function disableHtml(html,content,disab){
    $(html).attr('disabled',disab);
 	$(html).html(content);
}

function display_stat(softid,status,flag){
	var div_id_arr = ["down_$sid_recommend","down_$sid_hotapp","down_$sid_hotgame","down_$sid_newapp","down_$sid_newgame","down_$sid_netgame","down_$sid_1d","down_$sid_7d","down_$sid_30d","down_$sid_sort","down_$sid_list","down_$sid_like","down_$sid_subject_index","down_$sid_subject_list","down_$sid_subject","down_$sid_must","down_$sid_detail","down_$sid_app","down_$sid_game","down_$sid_search"];
	var status_arr = [install,installing,downloading,installed,upgrade];
	var detail_status_arr = [de_install,de_installing,de_downloading,de_installed,de_upgrade];
	var state = status - 1;
	var grep_flag = /detail/i;
	for(var i in div_id_arr){
		var div_id = div_id_arr[i].replace(/\$sid/, softid);		
		if(grep_flag.test(div_id)){
			status_img = detail_status_arr[state];
		}else{
			status_img = status_arr[state];
		}		
		disableHtml("#" + div_id + " > a",status_img,flag);
	}
}
