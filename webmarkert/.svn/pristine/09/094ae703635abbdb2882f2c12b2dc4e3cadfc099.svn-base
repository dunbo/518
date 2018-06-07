/*绑定开始*/
function scene(app) {  
	this.app = app;  
}  

scene.prototype = {
	id_list:['$sid_a', 'hot_$sid_a', 'new_$sid_a', '1d_$sid_a', '7d_$sid_a', '30d_$sid_a', 'sub_h_$sid_a', 'sub_v_$sid_a','sort_1_$sid_a','sort_2_$sid_a','sort_3_$sid_a'],
	batch_set:function(soft_id, className, disabled) {
		for(var i in this.id_list) {
			var id = this.id_list[i].replace(/\$sid/, soft_id);
			var obj = document.getElementById(id);
			if(obj != null){
				obj.className = className;
				obj.disabled = disabled;
			}
		}
	},
	wtj:function(params){//未添加的显示添加字样
		this.batch_set(params['soft_id'], this.app.style.add, false);
	},
	xzz:function(params){//下载中
		this.batch_set(params['soft_id'], this.app.style.adding, true);
	},
	azz:function(params){//安装中
		this.batch_set(params['soft_id'], this.app.style.installing, true);
	},
	yjaz:function(params){//一键安装
		this.batch_set(params['soft_id'], this.app.style.oneInstall, false);
	},
	ytj:function(params){//已添加
		this.batch_set(params['soft_id'], this.app.style.open, true);
	}
};
/*绑定结束*/
function App(){
	this.style = {add:"ins05",adding:"ins04",installing:"ins03",oneInstall:"ins01",open:"ins02"};
	this.t_time = null;
	this.softArray=[];
	this.soft_num=3;
	this.first = true;
	this.get_num = 0;
	this.scene = new scene(this);
	this.g_soft_info={};
}
App.prototype = {
	getSoftStatus : function() {
		var _self = this;
		this.t_time = window.setInterval(function() {_self.setSoftStatus();},200);
	},
	setSoftStatus:function(){//每隔一段时间去取状态，把压力放在页面处理，防止客户端阻塞导致卡死现象。
		var len = this.softArray.length;
		if(len == 0){
			if(this.first){
				if(this.get_num>30){//因为客户端只给有状态的信息，所以没有办法区分是取完了，还是没有状态，所以尝试30次后停止。
					clearInterval(this.t_time);
					return;
				}else{
					this.get_num++;
					return;
				}
			}else{
				clearInterval(this.t_time);
				return;
			}
		}
		this.first = false;
		for( var i=0;i<this.soft_num;i++){
			try{
				this.displaySoftStatus({'soft_id':this.softArray[i][0],'soft_status':this.softArray[i][1]});
			}catch(e){
				continue;
			}
		}
		this.softArray.splice(0, this.soft_num);
	},
	//初始化显示状态
	displaySoftStatus:function(params){
		var soft_id = params['soft_id'];
		switch( params['soft_status'] ){
			case 1: //已添加
				/*接口开始*/
				this.scene.ytj({'soft_id':soft_id});
				/*接口结束*/
				break;
			case 3: //一键安装
				/*接口开始*/
				this.scene.yjaz({'soft_id':soft_id});
				/*接口结束*/
				break;
			default: //未添加的显示添加字样
				/*接口开始*/
				this.scene.wtj({'soft_id':soft_id});
				/*接口结束*/
				break;
		}
	},
	downloadSoft:function(params){//下载软件
		var soft_id = params['id'];  //该ID是页面点击下载按钮那里，传进来一个软件ID，说明是要下载哪个
		var type = params['type'] ? params['type'] : 'download';
		var soft_name = '';
		//每个软件下载按钮点击后，传一个ID的参数，用这个ID去软件数据的对象列表里查询复合的软件
		var cur_status_obj = this.g_soft_info[soft_id];

		var cur_status = "{";
		cur_status +="\""+soft_id+"\":{";
		for( var key in cur_status_obj ){
			cur_status +="\""+key+"\":\""+cur_status_obj[key]+"\",";
		}
		cur_status +="\"\":\"\"}";
		cur_status +="}";
		var client_p = type+"|"+cur_status+"|"+soft_name;
		try{window.external.SoftMgr_Notify(2,client_p);}catch(e){}
	},
	//下载过程显示状态
	displayDownloadStatus:function(params){
		var soft_id = params['soft_id'];
		var content = parseInt(params['content']);
		switch(content){
			case 9: //取消了安装
				/*接口开始*/
				this.scene.wtj({'soft_id':soft_id});
				/*接口结束*/
				break;
			case 3: //下载中
				/*接口开始*/
				this.scene.xzz({'soft_id':soft_id});
				/*接口结束*/
				break;
			case 7: //安装中
				/*接口开始*/
				this.scene.azz({'soft_id':soft_id});
				/*接口结束*/
				break;
			case 11: //一键安装进度条
				/*接口开始*/
				this.scene.azz({'soft_id':soft_id});
				/*接口结束*/
				break;
			case 13: //一键安装后打开软件
				/*接口开始*/
				this.scene.ytj({'soft_id':soft_id});
				/*接口结束*/
				break;
		}
	}
}

var App1=new App();
/*客户端调用JS开始*/
function setSoftStatus(params){//初始化状态，当问客户端获取状态后，客户端会回调此方法，给页面状态
	try{
		for( var soft_id in params ){
			App1.softArray.push(new Array(soft_id,parseInt(params[soft_id])));
		}
		//开始获取软件状态，这里是为了兼容新版的今日热门客户端才这么做，管家和桌面处理的不同。
		App1.getSoftStatus();
	}catch(e){}
}
function setDownloadStatus(params){//下载过程状态
	for( var soft_id in params ){
		App1.displayDownloadStatus({'soft_id':soft_id,'content':params[soft_id]});
	}
}
/*客户端调用JS结束*/
function a(params){
	if(params['id']){
		App1.downloadSoft(params);
	}
	return;
}
