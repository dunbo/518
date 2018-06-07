$(document).ready(function(){
	/*****************update:2011-8-23 billyzheng*****************/
	$(".ztflashimg").width($("#advimages").width());
	var url=new Array();
		$(".ztflashimg").each(function(index2,item){
			 url[index2]=$(item).attr("url");
		});
	var length=$(".ztflashimg").length;
	var auto;
	$("#advimages").scrollLeft(0);
	
	window.index=0;
	var auto;
	var slider=function(left,obj){//滑动函数
		var _slider=$("#advimages");
		if(obj) _slider=$(obj);
		if(left == 0){
			_slider.stop().animate({scrollLeft:left},200);
		}else{
			_slider.stop().animate({scrollLeft:left},500);
		}
	};
	window.active=function(){	
		$("#storeimgs li").attr("class","");	
		$($("#storeimgs li").get(index)).addClass("flashhover");	
		slider(window.index*$(".ztflashimg").width());//切换图片
	    
	};
	
	window.set=function(){//自动切换
		auto=setInterval(function(){index=(++index)%$("#storeimgs li").length;active();},5000);
	};
	window.clear=function(){//清楚自动切换
		clearInterval(auto);
	};
	

	/***************************update end***********************/
	    $("#storeimgs li").mouseover(function(){
				    	window.index=$("#storeimgs li").index(this);
						clear();
						active();
						set();
					});
				    /*
					$("#storeimgs li").mouseout(function(){
						window.index=$("#storeimgs li").index(this);
						set();
					});
					*/			
					/***************************update end***********************/
					if($("#storeimgs li").length > 1){
						set();
		           }
		$(window).resize(function(){
	   $(".ztflashimg").width($("#advimages").width());
	})
});
