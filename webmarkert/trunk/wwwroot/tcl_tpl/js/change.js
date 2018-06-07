// JavaScript Document
var scope = new Array();
var userAppHash = new Object();
var appList = new Array();
function RefreshMarketInstallStatus() {
        if (parent.getApplication) {
            scope = parent.getApplication();
        }
        var x;
        for(x in scope){
            //豌豆夹 接口数据
            var pkg = scope[x].package;
            userAppHash[pkg] = scope[x]
        }
        for(x in appList) {
            var app = appList[x]
            installationOn(app.id, app.package, app.versionCode)
        }
    }

    function installationOn(softid,package,vercode){
        // 0: to install
        // 1: to update
        // 2: installed
        var exi = getInstallStatus(package, vercode);
        if(exi == 0){
            $("#down_"+softid + "_recommend > a").html("安装");
            $("#down_"+softid + "_netgame > a").html("安装");
            $("#down_"+softid + "_hotapp > a").html("安装");
            $("#down_"+softid + "_hotgame > a").html("安装");
            $("#down_"+softid + "_newapp > a").html("安装");
            $("#down_"+softid + "_newgame > a").html("安装");
			$("#down_"+softid + "_app").html("<span>已安装</span>");
            $("#down_"+softid + "_game").html("<span>已安装</span>");
            $("#down_"+softid + "_1d > a").html("安装");
            $("#down_"+softid + "_7d > a").html("安装");
            $("#down_"+softid + "_30d > a").html("安装");
			$("#down_"+softid + "_like > a").html("安装");
			$("#down_"+softid + "_subject > a").html("安装");
			$("#down_"+softid + "_search > a").html("安装");
			$("#down_"+softid + "_author > a").html("安装");
            $("#down_"+softid + " > a").html("安装");
        }
        if(exi == 1){
            $("#down_"+softid + "_recommend > a").html("更新");
            $("#down_"+softid + "_netgame > a").html("更新");
            $("#down_"+softid + "_hotapp > a").html("更新");
            $("#down_"+softid + "_hotgame > a").html("更新");
            $("#down_"+softid + "_newapp > a").html("更新");
            $("#down_"+softid + "_newgame > a").html("更新");
			$("#down_"+softid + "_app").html("<span>已安装</span>");
            $("#down_"+softid + "_game").html("<span>已安装</span>");
            $("#down_"+softid + "_1d > a").html("更新");
            $("#down_"+softid + "_7d > a").html("更新");
            $("#down_"+softid + "_30d > a").html("更新");
			$("#down_"+softid + "_like > a").html("更新");
			$("#down_"+softid + "_subject > a").html("更新");
			$("#down_"+softid + "_search > a").html("更新");
			$("#down_"+softid + "_author > a").html("安装");
            $("#down_"+softid + " > a").html("更新");
        }
        if(exi == 2){
            $("#down_"+softid + "_recommend").html("<span>已安装</span>");
            $("#down_"+softid + "_netgame").html("<span>已安装</span>");
            $("#down_"+softid + "_hotapp").html("<span>已安装</span>");
            $("#down_"+softid + "_hotgame").html("<span>已安装</span>");
            $("#down_"+softid + "_newapp").html("<span>已安装</span>");
            $("#down_"+softid + "_newgame").html("<span>已安装</span>");
			$("#down_"+softid + "_app").html("<span>已安装</span>");
            $("#down_"+softid + "_game").html("<span>已安装</span>");
            $("#down_"+softid + "_1d").html("<span>已安装</span>");
            $("#down_"+softid + "_7d").html("<span>已安装</span>");
            $("#down_"+softid + "_30d").html("<span>已安装</span>");
			$("#down_"+softid + "_like").html("<span>已安装</span>");
			$("#down_"+softid + "_subject").html("<span>已安装</span>");
			$("#down_"+softid + "_search").html("<span>已安装</span>");
			$("#down_"+softid + "_author").html("安装");
            $("#down_"+softid).html("<span>已安装</span>");
        }
    }

function GetId(id)
{
    return document.getElementById(id);
}

function getInstallStatus(package, versionCode) {
    // 0: to install
    // 1: to update
    // 2: installed
    var userAppInfo = userAppHash[package];

    if (!userAppInfo) return 0;
    if (parseInt(versionCode) <= parseInt(userAppInfo.versionCode)) { //判断当前版本是否最新
        return 2;
    } else if (parseInt(versionCode) > parseInt(userAppInfo.versionCode)) {
        return 1;
    }
    return 0;
}
function on_install (name, href) {
    if(href.indexOf("http") != 0) href="http://"+window.location.host+href;
	//new
	if(window.externalCall){
		var m = {};
		m.downloadUrl=href; // url
		m.name=name; // title
		window.externalCall('application', 'appdownload', JSON.stringify([m]));
	}else{
		alert(name+" "+href);
	 }
}
function changetab(id){

	var tab = $("#"+id+" .titlebar ul li");
	var ulObj = $("#"+id+" .yylist");
	tab.mouseover(function(){
					var index = $(this).index();
					ulObj.eq(index).show().siblings("ul").hide();
					tab.eq(index).addClass("selected").siblings("li").removeClass("selected");
						   })
		}

	function showdown(name){

		var tab = $("#"+name+"  li");

	tab.mouseover(function(){

					var index = $(this).index();
					tab.eq(index).addClass("selected").siblings("li").removeClass("selected");
					   })

	tab.mouseout(function(){

						var index = $(this).index();
					tab.eq(index).removeClass("selected");
						  })

		}

 function changetabs(li,obj1,obj2){

	 var tab = $(".title ul li");
	 var ulObj1 = $(".zjyylist .tjlist");
	 var ulObj2 = $(".imglist li");
	 tab.mouseover(function(){
					var index = $(this).index();
					tab.eq(index).addClass("selected").siblings("li").removeClass("selected");
					ulObj1.eq(index).show().siblings(".zjyylist .tjlist").hide();
					ulObj2.eq(index).addClass("selected").siblings("li").removeClass("selected");
							})

	 }

	 function getfocus(name,val){

		 var obj = $(name);
		 obj.blur(function(){
						   var strings = obj.val();
						   if(strings == "")
						   obj.val(val);
						   else return;
						   })

		 obj.focus(function(){
				var strings = obj.val();
				if(strings == val)
				obj.val("");
				else return;
							 })
		 }

		 function getfocusTxt(id){

			 var obj = $(id);

			 obj.focus(function(){
								obj.addClass("onfocurs").removeClass("ltxt");
								})
			 obj.blur(function(){
							   obj.removeClass("onfocurs").addClass("ltxt");
							   })

			 }


