function login(login_url,version_code){
	if(version_code >= 5700 ){
		window.AnzhiActivitys.login();
		javascript:window.history.forward(1); 
		//logout();
	}else{
		location.href=login_url;
	}
}

//弹窗
function pop_tips(title,notice,box_id,is_center){
	$("#title"+box_id).html(title);	
	$("#notice"+box_id).html(notice);
	showOpenBox('#tip-box'+box_id,is_center);					
	return false;	
}

var flags={canMove:true};
function showOpenBox(obj,is_center){
	if(is_center == 1){
		var bg_h=$(window).height() + $(document).scrollTop(),
			top_h= $(obj).height()/ 2 - $(document).scrollTop();
		$(obj).css("margin-top",-top_h+"px").show();;
		$('#body-bg').css("height",bg_h+"px").show();
		flags.canMove=false;
		window.onresize = function(){
			var bg_h=$(window).height() + $(document).scrollTop(),
				top_h= $(obj).height()/ 2 - $(document).scrollTop();
			$('#body-bg').css("height",bg_h+"px");
			$(obj).css("margin-top",-top_h+"px");	
		}		
	}else{
		var bg_h=$(window).height()+$(document).scrollTop(),
			top_h= $(document).scrollTop();
		$(obj).css("top",top_h+"px").show();
		$('#body-bg').css("height",bg_h+"px").show();
		flags.canMove=false;
		window.onresize = function(){
		var bg_h=$(window).height()+$(document).scrollTop();
			$('#body-bg').css("height",bg_h+"px");
		}
	}
}
function cloBox(obj,is_reload){
	$(obj).hide();
	 $('#body-bg').hide();
	flags.canMove=true;
	if(is_reload == 1){
		//location.assign(location) ;
		window.location.reload();//加载页面
	}
}

//优化返回按钮
$(function(){
    $("body").bind('touchmove', function (e) {
        if(!flags.canMove){
            e.preventDefault();
        }
    });
})
