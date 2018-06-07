var app = {
	Config: {
		container:'.changewidth_box',
		con: '.recommend',
		subject:".ztindex",
		conbj:'#changewidth_box1'
	},
	resize:function(){
		var wind = $(app.Config.container).width();
		var container=$(app.Config.conbj);
			app.setconwidth(app.Config.con);
			//alert(wind);
			$(app.Config.con).find("li").each(function(index,item){
			  if(wind>540&&wind<736){
					var space=Math.floor(wind/5);
					$(item).width(space-2);
				}
			   if(wind==540){
				    $(item).width(105);
			  }
			  if(wind==736){
				  $(item).width(120);
			 }
		 });
	},
	setconwidth:function(con){
		var wind = $(app.Config.container).width();
       if(wind==540){
		 	$(con).width(531);
		}
		else if(wind==736){
			$(con).width(726);
		}else{//每个item的宽度
			  var space=Math.floor(wind/5);  
			  $(con).width(space*5);//整个页面的宽度
		}
	},
	init:function(options){

		app.Config=$.extend(app.Config,options);
		app.resize();
		$(".flashimg").width($(".images").width());
		$(window).resize(function(){
			app.Config=$.extend(app.Config,options);
		app.resize();
	    $(".flashimg").width($(".images").width());
		});
   },
   initie6:function(options){
	   app.Config=$.extend(app.Config,options);
	   $(app.Config.con).find("li").each(function(index,item){
			 $(item).width(105);
		 });
   }
  
    }
function ckie6(){
	if(window.ActiveXObject){
		var browser=navigator.appName 
        var b_version=navigator.appVersion 
        var version=b_version.split(";"); 
        var trim_Version=version[1].replace(/[ ]/g,""); 
			if(browser=="Microsoft Internet Explorer" && trim_Version=="MSIE6.0") 
        {  
			return true;
        }else{
			return false;
		}
	}
}
function chk(num){ //判断是否是奇数
  if(num%2){
	  return true;
  }
  else{
	  return false
 }
} 
$(document).ready(function(){
	//设置安装，更新按钮的状态
	if(ckie6()){
  			var opt1={"container":"#changewidth_box1","con":".recommend"};
       		app.initie6(opt1);
		
	}else{
		    var opt1={"container":"#changewidth_box1","con":".recommend"};
       		app.init(opt1);
	}
  
 });