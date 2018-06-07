function getArgs() {
    var args = {};
    var query = location.search.substring(1);
    var pairs = query.split("&");
    for(var i = 0; i < pairs.length; i++) {
        var pos = pairs[i].indexOf('=');
        if (pos == -1) continue;
        var argname = pairs[i].substring(0,pos);
        var value = pairs[i].substring(pos+1);
        value = decodeURIComponent(value);
        args[argname] = value;
    }
    return args;
}
var InAnzhiMarket = false;
if(typeof(window.AnzhiActivitys) == "undefined"){
	var AnzhiActivitys = (function () {
		function Construct() {
			//活动软件
			this.getAllAppList = function(aid){
				var str =  '';
				$.ajax({
					url: '/phone.php?act=getlistbyaid',
					data:'aid='+aid,
					type:"get",
					async: false,			
					success:function(data) {
						str = data;
					},  			
				});	
				return str;
			};
			//签到软件
			this.getAllSignAppList = function(mid){
				var str =  '';
				$.ajax({
					url: '/phone.php?act=getsignlistbymid',
					data:'mid='+mid,
					type:"get",
					async: false,			
					success:function(data) {
						str = data;
					},  			
				});	
				return str;
			};
			//toast
			this.showToastForJs = function(str){
				alert(str)	
				console.log(str);
			};
			//copy
			this.copyText = function(str){
				alert(str)	
				console.log(str);
			};
			//签到
			this.userSign = function(){	
				var red_json = '{"type":"1","result":"1","packetId":"0"}';
				reportPacketResult(red_json);
			};

			//签到拆红包or红包活动拆红包
			this.openRedPack = function(json){
				//结果回调：签到结果、拆红包结果（活动or签到）
				//var red_json = '{"type":"2","result":"1","packetId":"'+json.redPackId+'"}';
				//reportPacketResult(red_json);
				console.log(json);
			};
			//打开新页面（支持页面跳转）
			this.loadUrlNextPage = function(json){
				var obj = JSON.parse(json);
				//console.log(obj);
				location.href=obj.url;
			}
			//修改右侧actionBar,紧支持文本
			this.updateRightActionBar = function(json){
				console.log(json);
			}
			//是否打开签到提醒 json为空表示关闭签到提醒
			this.signAlarm = function(json){
				console.log(json);
			}
			//签到分享
			this.getSignShareDialog = function(json){
				alert('签到分享成功');
			}
			//
			this.registerDownloadObserver = function(softid){
				//console.log(softid);
			};
			this.registerInstallObserver = function(pkg){
				//console.log(pkg);
			};
			this.isInstalledApp = function(pkg,versioncode){
				//return pkg_arr;
				return -1;
			};
			this.getDownloadStateForActivity = function(softid){
				//console.log(softid);
				//var arr_num = [1,2,3,4,5,6,8];
				//var num= arr_num[Math.floor(Math.random()*arr_num.length)];
				//console.log(num);
				//return num;
				return 4;
			};
			this.openAppForActivity = function(pkg,softid){
				//console.log('打开');
				alert('打开成功');
				return false;
			};
			this.downloadForActivity = function(aid,softid,my_package,softname,versioncode,size,details){
				alert('下载成功');
				console.log('下载');
				onDownloadStateChanged(softid,1);
				setTimeout(function(){
					onDownloadStateChanged(softid,5);
				}, 3000);			
			};
			this.downloadAppsForActivity = function(aid,feature_id,json){
				alert('批量下载成功');
				console.log('下载');
				/*onDownloadStateChanged(softid,1);
				setTimeout(function(){
					onDownloadStateChanged(softid,5);
				}, 3000);*/			
			};
			this.installAppForActivity = function(softid){
				alert('安装成功');
				console.log('安装');
				onDownloadStateChanged(softid,6);
			};
			this.getShareDialog = function(str){
				alert('分享成功');
			};
			this.addSubjectCommentPraise = function(id,count){
				
			};
			this.isSubjectCommentPraised = function(id,count){
				
			};
			this.isCommentPraised = function(commentid,pkg){
				
			};
			this.getCommentPraiseCount = function(commentid,pkg){
				
			};
			this.addCommentPraise = function(commentid,pkg,count){
				
			};
			this.isNetworkDisabled = function(){
				
			};
			this.loadUrlNextPage = function(url_json){
				var obj = JSON.parse(url_json);
				location.href=obj.url;
			};
			this.postCommentOrReplyProtocol = function(content,type,commentfrom,id,pkg,commentData)
			{
				alert("提交成功！");
			};
			this.launch = function(js_param, php_param)
			{
					
			};
			this.getSubjectDetail = function(offset, size){
				var p = getArgs();
				var str =  '';
				if (typeof(p['id']) != 'undefined') {
					$.ajax({
						url: '/phone.php?act=getSubjectDetail',
						data:'feature_id='+p['id']+'&offset='+offset+'&size='+size,
						type:"get",
						async: false,
						success:function(data) {
							str = data;
						},  			
					});
				}
				return str;
			};
			this.getSubjectCommentList = function(offset, size){
				var p = getArgs();
				var str =  '';
				if (typeof(p['id']) != 'undefined') {
					$.ajax({
						url: '/phone.php?act=getSubjectCommentList',
						data:'feature_id='+p['id']+'&offset='+offset+'&size='+size,
						type:"get",
						async: false,
						success:function(data) {
							str = data;
						},  			
					});
				}
				return str;
			};
			
		}
		var obj_o = new Construct();
		return obj_o;
	})();		
}else{
    InAnzhiMarket = true;
}
