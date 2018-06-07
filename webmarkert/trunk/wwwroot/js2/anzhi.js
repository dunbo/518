// JavaScript Document


$.prototype.loadAPK = function(url, callback){
	var load_html = '<div class="loading"><img src="/images/loading.gif" alt="加载中" /></div>';
	$(this).before(load_html);
	$.prototype.load.call(this, url, function() {
	
		rebind_obj(this); 
		
		if (typeof callback == 'function') {
			callback();
		}
	});
}

$(document).ready(function(){

    /*头部关注微信弹框*/
    $(".topbar .wx_icon").hover(function(){
        $(".wx_pop").show();
    },function(){
        $(".wx_pop").hide();
    });
    /*应用立即下载弹出二维码*/
    $(".app_down a").hover(function(){
        $(this).next(".pop_code").show();
		if(!$(this).next(".pop_code").has('src').length){
			$(this).next(".pop_code").html('<img src="'+$(this).next(".pop_code").attr('rel')+'">');
		}
    },function(){
        $(this).next(".pop_code").hide();
    });

	$('.recommend2 li').bind({mouseover:function(){
		$(this).children('.down_num').css('display','none');
		$(this).children('.recommend2_down').css('display','block');
	},
	mouseout:function(){
		$(this).children('.down_num').css('display','block');
		$(this).children('.recommend2_down').css('display','none');
	}
	});
	$('.install_zt_list li').bind({mouseover:function(){
		$(this).find('.install_down').css('display','none');
		$(this).find('.down').css('display','block');
	},
	mouseout:function(){
		$(this).find('.install_down').css('display','block');
		$(this).find('.down').css('display','none');
	}
	});
});
//详情页内容展开收起
function conHideShow(obj,hgt,cls){
    $(obj).each(function(){
        var _this=$(this),
            moreCon=$(this).next(".morecontent"),
            moreSpan=moreCon.children("span"),
            clickFn=function(){
                if(moreSpan.hasClass(cls))
                {
                    _this.css('height',hgt+"px");
                    moreSpan.removeClass(cls);
                }
                else{
                    _this.css('height','auto');
                    moreSpan.addClass(cls);
                }
            }
            if (_this.find("p").height() <= hgt) {
                moreCon.hide();
                _this.css("cursor","default");
            }else{
                _this.bind('click',clickFn);
                moreCon.bind('click',clickFn);
            }
    });
}
function rebind_obj(o) {
	var curr_obj = o.parentNode;
	$(o).prev('.loading').remove();
	$(curr_obj).find('.recommend li').bind({mouseover:function(){
			$(this).css('backgroundColor','#eff1f3');
		},
		mouseout:function(){
			$(this).css('backgroundColor','');
		}
	});
	$(curr_obj).find('.recommend li').bind({mouseover:function(){
			$(this).children('.stars').css('display','none');
			$(this).children('.down').css('display','block');
		},
		mouseout:function(){
			$(this).children('.stars').css('display','block');
			$(this).children('.down').css('display','none');
		}
	});
	$(curr_obj).find('.recommend2 li').bind({mouseover:function(){
			$(this).children('.down_num').css('display','none');
			$(this).children('.recommend2_down').css('display','block');
		},
		mouseout:function(){
			$(this).children('.down_num').css('display','block');
			$(this).children('.recommend2_down').css('display','none');
		}
	});
	$(curr_obj).find('.install_zt_list li').bind({mouseover:function(){
			$(this).find('.install_down').css('display','none');
			$(this).find('.down').css('display','block');
		},
		mouseout:function(){
			$(this).find('.install_down').css('display','block');
			$(this).find('.down').css('display','none');
		}
	});
}

function showOpen(id,clolseId,closeId2){
	var oDiv = document.getElementById(id);
	if(oDiv){
		oDiv.style.display = 'block';
		oDiv.style.top = (viewHeight() - oDiv.offsetHeight)/2 + scrollY() + 'px';
		oDiv.style.left = (viewWidth() - oDiv.offsetWidth)/2 + 'px';
	}
	var oMark = document.createElement('div');
	oMark.id = 'mark';
	document.body.appendChild(oMark);
	oMark.style.width = viewWidth() + 'px';
	oMark.style.height = documentHeight() + 'px';
	closeLogin();
	function closeLogin(){
		var oClose = document.getElementById(clolseId);
		var oClose2 = document.getElementById(closeId2);
        if(oClose!=null ){
            oClose2.onclick = oClose.onclick = function(){
                document.body.removeChild(oMark);
                oDiv.style.display = 'none';
            };
        }else{
            oClose2.onclick = function(){
                document.body.removeChild(oMark);
                oDiv.style.display = 'none';
            };

        }

    }
	window.onresize = window.onscroll = function(){
		oMark.style.width = viewWidth() + 'px';
		oMark.style.height = documentHeight() + 'px';
		var oDiv = document.getElementById(id);
		if(oDiv){
			oDiv.style.top = (viewHeight() - oDiv.offsetHeight)/2 + scrollY() + 'px';
			oDiv.style.left = (viewWidth() - oDiv.offsetWidth)/2 + 'px';
		}
	}
}
function viewWidth(){
	return document.documentElement.clientWidth;
}
function viewHeight(){
	return document.documentElement.clientHeight;
}
function scrollY(){
	return document.documentElement.scrollTop || document.body.scrollTop;
}
function documentHeight(){
	return Math.max(document.documentElement.scrollHeight || document.body.scrollHeight,document.documentElement.clientHeight);
}
function showorhide1(obj){
	obj.className ='market_phone';
	$('#market_hd').attr('class','market_hd');
	$('#market_list1').css('display','block');
	$('#market_list2').css('display','none');
	
}
function showorhide2(obj){
	obj.className ='market_hd2';
	$('#market_phone').attr('class','market_phone2');
	$('#market_list2').css('display','block');
	$('#market_list1').css('display','none');
}
function sidetab(obj,id,classname){
	$('.'+classname).css('display','none');
	$('#'+classname+id).css('display','block');
    $(obj).siblings("li").removeClass("current");
    $(obj).addClass("current");
//	if(id==1)
//	{
//		$(obj).parent().css('backgroundPosition','0 0');
//	}
//	else{
//		$(obj).parent().css('backgroundPosition','0 -28px');
//	}

}
function sidetabzt(obj,id,classname){
	$('.'+classname).css('display','none');
	$('#'+classname+id).css('display','block');
	$('#'+classname+id).css('display','block');
	if(id==1)
	{
		$(obj).parent().css('backgroundPosition','0 0');
	}
	else{
		$(obj).parent().css('backgroundPosition','0 -45px');
	}

}
function sidetab2(obj,id,classname){
	$('.'+classname).css('display','none');
	$('#'+classname+id).css('display','block');
    $(obj).siblings("li").removeClass("current");
    $(obj).addClass("current");
//	if(id==1)
//	{
//
//		$(obj).parent().css('backgroundPosition','0 -84px');
//	}
//	else if(id==2)
//	{
//		$(obj).parent().css('backgroundPosition','0 -56px');
//	}
//	else{
//		$(obj).parent().css('backgroundPosition','0 -112px');
//	}
}
function slider1(ulId,olId)
{
	var oUl = document.getElementById(ulId);
	var oOl = document.getElementById(olId);
	var aLi = oUl.getElementsByTagName('li');
	var aLi1 = oOl.getElementsByTagName('li');
	var iNow = 0;
	var timer = null;
	if(aLi.length ==0 || aLi1.length==0)
	{
		return;
	}
	oUl.style.width = aLi.length * aLi[0].offsetWidth + 'px';
	//自动切换
	timer = setInterval(moveNext,5000);
	function moveNext(){
		if(iNow ==aLi1.length-1 ){
			iNow =0;
		}
		else{
			iNow++;
		}
		for(var i=0; i<aLi1.length; i++)
		{
			aLi1[i].className="";
		}
		aLi1[iNow].className ="current";
		startMove(oUl,{'left':-iNow*aLi[0].offsetWidth});
	}
	//点击切换
	for(var i=0; i<aLi1.length; i++)
	{
		aLi1[i].index = i;
		aLi1[i].onmouseover = function(){
			iNow = this.index;
			for(var i=0; i<aLi1.length; i++){
				aLi1[i].className ='';
			}
			aLi1[iNow].className ='current';
			startMove(oUl,{'left':-iNow*aLi[0].offsetWidth});
			clearInterval(timer);
		}
		aLi1[i].onmouseout = function()
		{
			timer= setInterval(moveNext,5000);
		}
	}
}

function slider2(ulId,btn_left,btn_right)
{
	var oUl = document.getElementById(ulId);
	var aLi = oUl.getElementsByTagName('li');
	var btnLeft = document.getElementById(btn_left);
	var btnRight = document.getElementById(btn_right);
	var iNow = 0;
	var timer = null;
	if(aLi.length ==0)
	{
		return;
	}
	oUl.style.width = aLi.length * aLi[0].offsetWidth + 'px';
	if(aLi.length<=3)
	{
		btnRight.style.backgroundImage = 'url(/images/arrow_07.png)';
		return;
		}
	btnRight.onclick = function(){
		if(iNow ==aLi.length-3 ){
			return;
		}
		else{
			iNow++;
		}
		startMove(oUl,{'left':-iNow*aLi[0].offsetWidth});
		if(btnLeft)
		{
			if(iNow!=0)
			{
				btnLeft.style.backgroundImage = 'url(/images/arrow_04.png)'
			}
			else{
				btnLeft.style.backgroundImage = 'url(/images/arrow_08.png)'
			}
			if(iNow==aLi.length-3)
			{
				btnRight.style.backgroundImage = 'url(/images/arrow_07.png)'
			}
			else{
				btnRight.style.backgroundImage = 'url(/images/arrow_03.png)'
			}
		}
	}
	btnLeft.onclick = function(){
		if(iNow==0)
		{
			return;
		}
		iNow--;
		startMove(oUl,{'left':-iNow*aLi[0].offsetWidth});
		if(iNow!=0)
			{
				btnLeft.style.backgroundImage = 'url(/images/arrow_04.png)'
			}
			else{
				btnLeft.style.backgroundImage = 'url(/images/arrow_08.png)'
			}
			if(iNow==aLi.length-3)
			{
				btnRight.style.backgroundImage = 'url(/images/arrow_07.png)'
			}
			else{
				btnRight.style.backgroundImage = 'url(/images/arrow_03.png)'
			}
	}
}
function slider4(ulId,btn_left,btn_right)
{
	var oUl = document.getElementById(ulId);
	var aLi = oUl.getElementsByTagName('li');
	var btnLeft = document.getElementById(btn_left);
	var btnRight = document.getElementById(btn_right);
	var iNow = 0;
	var timer = null;
	if(aLi.length ==0)
	{
		return;
	}
	oUl.style.width = aLi.length * aLi[0].offsetWidth + 'px';
	if(aLi.length<=2)
	{
		btnRight.style.backgroundImage = 'url(/images/arrow_07.png)';
		return;
		}
	btnRight.onclick = function(){
		if(iNow ==aLi.length-2 ){
			return;
		}
		else{
			iNow++;
		}
		startMove(oUl,{'left':-iNow*aLi[0].offsetWidth});
		if(btnLeft)
		{
			if(iNow!=0)
			{
				btnLeft.style.backgroundImage = 'url(/images/arrow_04.png)'
			}
			else{
				btnLeft.style.backgroundImage = 'url(/images/arrow_08.png)'
			}
			if(iNow==aLi.length-2)
			{
				btnRight.style.backgroundImage = 'url(/images/arrow_07.png)'
			}
			else{
				btnRight.style.backgroundImage = 'url(/images/arrow_03.png)'
			}
		}
	}
	btnLeft.onclick = function(){
		if(iNow==0)
		{
			return;
		}
		iNow--;
		startMove(oUl,{'left':-iNow*aLi[0].offsetWidth});
		if(iNow!=0)
			{
				btnLeft.style.backgroundImage = 'url(/images/arrow_04.png)'
			}
			else{
				btnLeft.style.backgroundImage = 'url(/images/arrow_08.png)'
			}
			if(iNow==aLi.length-2)
			{
				btnRight.style.backgroundImage = 'url(/images/arrow_07.png)'
			}
			else{
				btnRight.style.backgroundImage = 'url(/images/arrow_03.png)'
			}
	}
}
function slider3(ulId,btn_left,btn_right)
{
	var oUl = document.getElementById(ulId);
	var aLi = oUl.getElementsByTagName('li');
	var btnLeft = document.getElementById(btn_left);
	var btnRight = document.getElementById(btn_right);
	var iNow = 0;
	var timer = null;
	if(aLi.length ==0)
	{
		return;
	}
	oUl.innerHTML += oUl.innerHTML;
	oUl.style.width = aLi.length * aLi[0].offsetWidth + 'px';
	btnRight.onclick = function(){
		if(iNow ==aLi.length-3 ){
			iNow =parseInt((aLi.length-3)/2);
		}
		else{
			iNow++;
		}
		startMove(oUl,{'left':-(iNow*aLi[0].offsetWidth+110)});
	}
	btnLeft.onclick = function(){
		if(iNow==0)
		{
			iNow =parseInt(aLi.length/2)-1;
		}
		else{
		iNow--;
		}
		startMove(oUl,{'left':-(iNow*aLi[0].offsetWidth+110)});
	}
}

function tab(obj,id,classname){
	$(obj).parent().children().removeClass('currentcolor').find("span").hide();
	$(obj).addClass('currentcolor').find("span").show();
	$('.'+classname).css('display','none');
	$('#'+classname+id).css('display','block');
}

function slider_detail(ulId,btn_left,btn_right)
{
	var oUl = document.getElementById(ulId);
	var aLi = oUl.getElementsByTagName('li');
	var btnLeft = document.getElementById(btn_left);
	var btnRight = document.getElementById(btn_right);
	var iNow = 0;
	var timer = null;
	if(aLi.length ==0)
	{
		return;
	}
	oUl.style.width = aLi.length * aLi[0].offsetWidth + 'px';
	if(aLi.length<=3)
	{
		btnRight.style.backgroundImage = 'url(images/slider_detail_right01.png)';
		return;
		}
	btnRight.onclick = function(){
		if(iNow ==aLi.length-3 ){
			return;
		}
		else{
			iNow++;
		}
		startMove(oUl,{'left':-iNow*aLi[0].offsetWidth});
		if(btnLeft)
		{
			if(iNow!=0)
			{
				btnLeft.style.backgroundImage = 'url(images/slider_detail_left02.png)'
			}
			else{
				btnLeft.style.backgroundImage = 'url(images/slider_detail_left01.png)'
			}
			if(iNow==aLi.length-3)
			{
				btnRight.style.backgroundImage = 'url(images/slider_detail_right01.png)'
			}
			else{
				btnRight.style.backgroundImage = 'url(images/slider_detail_right02.png)'
			}
		}
	}
	btnLeft.onclick = function(){
		if(iNow==0)
		{
			return;
		}
		iNow--;
		startMove(oUl,{'left':-iNow*aLi[0].offsetWidth});
		if(iNow!=0)
			{
				btnLeft.style.backgroundImage = 'url(images/slider_detail_left02.png)'
			}
			else{
				btnLeft.style.backgroundImage = 'url(images/slider_detail_left01.png)'
			}
			if(iNow==aLi.length-3)
			{
				btnRight.style.backgroundImage = 'url(images/slider_detail_right01.png)'
			}
			else{
				btnRight.style.backgroundImage = 'url(images/slider_detail_right02.png)'
			}
	}
}

function showOpenNew(id){
	var oDiv = document.getElementById(id);
	if(oDiv){
		oDiv.style.display = 'block';
		oDiv.style.top = (viewHeight() - oDiv.offsetHeight)/2 + scrollY() + 'px';
		oDiv.style.left = (viewWidth() - oDiv.offsetWidth)/2 + 'px';
	}
	var oMark = document.createElement('div');
	oMark.id = 'mark';
	oMark.innerHTML = '<iframe id="DivShim" scrolling="no" frameborder="0" src="" style="position:absolute; top:0px; left:0px; width:100%; height:100%; background:none; filter:alpha(opacity=0); opacity:0; border:none;" frameborder="no"></iframe>';
	document.body.appendChild(oMark);
	oMark.style.width = viewWidth() + 'px';
	oMark.style.height = documentHeight() + 'px';
	window.onresize = window.onscroll = function(){
		oMark.style.width = viewWidth() + 'px';
		oMark.style.height = documentHeight() + 'px';
		var oDiv = document.getElementById(id);
		if(oDiv){
			oDiv.style.top = (viewHeight() - oDiv.offsetHeight)/2 + scrollY() + 'px';
			oDiv.style.left = (viewWidth() - oDiv.offsetWidth)/2 + 'px';
		}
	}
		
}
function closeBtn(id){
	var oMark = document.getElementById('mark');
			var oDiv = document.getElementById(id);
			oDiv.style.display = 'none';	
			document.body.removeChild(oMark);
	}
function getpwd_email(){
	var username = $("#username").val();
	var oDiv1 = document.getElementById('getpwd_email');
	var oDiv2 = document.getElementById('getpwd_phone');

	if(username=='')
	{
		oDiv1.style.display='none';
		oDiv2.style.display='none';
	}else{
		oDiv1.style.display='block';
		oDiv2.style.display='none';
	}
	
}
function getpwd_phone(){
	var username = $("#username").val();
	var oDiv1 = document.getElementById('getpwd_email');
	var oDiv2 = document.getElementById('getpwd_phone');
	if(username=='')
	{
		oDiv1.style.display='none';
		oDiv2.style.display='none';
	}else{
		oDiv1.style.display='none';
		oDiv2.style.display='block';
	}
	
}


function detection()
{	
	var username = $("#username").val();
	var number = Math.random();
	if(username =='')
  	{
  		$('#hint').html('为确保您能收到激活邮件，请准确填写。').css({color : '#CE0421'});
  		$('#selemail').css('display','none');
  		$('#oneemail').css('display','none');
  		$('#getpwd_email').css('display','none');
  		$('#phone').attr('disabled',true);
  		$('#_user_name3_mark').html('<img src="/images/error_icon.png" alt="" />');
  		return false;
  	}else if(username.toLowerCase()=='admin'){
  		$('#hint').html('用户名不合法').css({color : '#CE0421'});
  		$('#phone').attr('disabled',true);
  		$('#getpwd_phone').css('display','none');
  		$('#email_sel').attr('disabled',true);
  		$('#getpwd_email').css('display','none');
  		$('#selemail').css('display','none');
  		$('#oneemail').css('display','none');
  		$('#_user_name3_mark').html('<img src="/images/error_icon.png" alt="" />');
  		return false;
  	}else{
  		$('#selemail').css('display','none');
  		$('#oneemail').css('display','none');
  		$('#getpwd_phone').css('display','none');
		$.ajax({
   			 type: "POST",
  			 url: "/forgetpass.php?number="+number,
  			 data: "username="+username+"&mark=3",
  			 dataType: "json",
  			 success: function(data){
  			 	$('#hint').html('');
  			 	if(data['mark_cnt'] >=1 && data['mark_cnt'] <= 3)//只有论坛邮箱或者论坛邮箱以及开发者邮箱
  			 	{
  			 		$('#getpwd_email').css('display','block');

  			 		if(data['mark_cnt'] == 1) {//只有论坛邮箱
  			 			$("#hint_e").html('');
  			 			$('#oneemail').css('display','block');
  			 			$('#selemail').css('display','none');
  			 			$('#email_sel').attr('checked','checked');
  			 			$('#on_email').html(data['email']);
  			 			$('#phone').attr('disabled',true);
  			 		}else if(data['mark_cnt'] == 2){
  			 			$('#oneemail').css('display','block');
  			 			$('#on_email').html(data['email']);
  			 			$("#hint_e").html('');
  			 		}else if(data['mark_cnt']==3){
  			 			if(data['bbs_email']==data['dev_email']){
  			 				$('#oneemail').css('display','block');
  			 				$('#selemail').css('display','none');
  			 				$('#email_sel').attr('checked','checked');
  			 				$('#on_email').html(data['b_email']);
  			 				$("#hint_e").html('');
  			 			}else{
	  			 			$('#oneemail').css('display','none');
	  			 			$('#selemail').css('display','block');
	  			 			$('#email_sel').attr('checked','checked');
	  			 			$('#show_dev').html(data['d_email']);
	  			 			$('#show_bbs').html(data['b_email']);
	  			 			$("#hint_e").html('您的账号存在多个注册邮箱，请选择一个邮箱找回密码').css({color : '#CE0421'});
  			 			}
  			 			$('#phone').attr('disabled',true);
  			 		}
  			 		$('#_user_name3_mark').html('<img src="/images/right_icon.png" alt="" />');
  			 	}else if(data['mark_cnt'] >= 5 && data['mark_cnt'] <= 7){
  			 		$('#getpwd_phone').css('display','none');
  			 		$('#getpwd_email').css('display','block');
  			 		$("#hint_e").html('');
  			 		if(data['mark_cnt'] == 5)
  			 		{
  			 			$('#selemail').css('display','none');
  			 			$('#oneemail').css('display','block');
  			 			$('#on_email').html(data['email']);
  			 		}else if(data['mark_cnt'] == 7){
  			 			$('#selemail').css('display','block');
  			 			$('#oneemail').css('display','none');
  			 			$('#show_dev').html(data['d_email']);
  			 			$('#show_bbs').html(data['b_email']);
  			 			$("#hint_e").html('您的账号存在多个注册邮箱，请选择一个邮箱找回密码').css({color : '#CE0421'});
  			 		}else{
  			 			$('#oneemail').css('display','block');
  			 		}
  			 		
  			 		$('#on_email').html(data['email']);
  			 		$('#email_sel').attr('checked','checked');
  			 		$('input[name="mobile"]').val(data['dev_mobile']);
  			 		$('#_user_name3_mark').html('<img src="/images/right_icon.png" alt="" />');
  			 	}else if(data['mark_cnt'] == 4){
  			 		$("#hint_e").html('');
    			 	$('#getpwd_email').css('display','none');
  			 		$('#email_sel').attr('disabled',true);
  			 		$('input[name="mobile"]').val(data['dev_mobile']);	
  			 		$('#_user_name3_mark').html('<img src="./images/right_icon.png" alt="" />');

  			 	}else if(data['mark_cnt'] == 12){
  			 		$("#hint_e").html('');
  			 		$('#getpwd_email').css('display','block');
  			 		$('#email_sel').attr('checked','checked');
  			 		$('#oneemail').css('display','block');
  			 		$('#selemail').css('display','none');
  			 		$('#on_email').html(data['b_email']);
  			 		$('#email_sel').attr('disabled',false);
  			 		$('#phone').attr('disabled',false);
  			 		$('input[name="mobile"]').val(data['dev_mobile']);
  			 		$('#_user_name3_mark').html('<img src="/images/right_icon.png" alt="" />');
  			 	}else if(data['mark_cnt'] == 0){
  			 		$("#hint_e").html('');
  			 		$('#phone').attr('disabled',true);
  			 		$('#getpwd_phone').css('display','none');
  			 		$('#email_sel').attr('disabled',true);
  			 		$('#getpwd_email').css('display','none');
  			 		$('#selemail').css('display','none');
  			 		$('#oneemail').css('display','none');
  			 		$('#hint').html('账号有误，请重新输入').css({color : '#CE0421'});;
  			 		$('#_user_name3_mark').html('<img src="/images/error_icon.png" alt="" />');
  			 		return false;
  			 	}else if(data['mark_cnt'] == 8){
  			 		$('#getpwd_email').css('display','block');
  			 		$('#email_sel').attr('checked','checked');
  			 		$('#oneemail').css('display','block');
  			 		$('#selemail').css('display','none');
  			 		$('#on_email').html(data['b_email']);
  			 		$('#phone').attr('disabled',true);
  			 		$('#_user_name3_mark').html('<img src="/images/right_icon.png" alt="" />');
  			 		$("#hint_e").html('');
  			 	}
  			 }
		});
	} 
}

function accounts()
{
	var username = $("#user_name_in").val();
	var reg=/^\d+$/;   
	var len = $("#user_name_in").val().replace(/[^\x00-\xff]/g, "**").length;
	var min;
	var max;
	min = 3;
	max = 15;
	if(username.match(/<|"%/ig)) {
		$("#accounts").html('用户名包含敏感字符').css({color : '#CE0421'});
		$('#_user_name_2_mark').html('<img src="/images/error_icon.png" alt="" />');
		return false;
	}else if(len < min){
		$("#accounts").html('账号不能少于三个字符').css({color : '#CE0421'});
		$('#_user_name_2_mark').html('<img src="/images/error_icon.png" alt="" />');
		return false;
	}else if(len > max){
		$("#accounts").html('账号不能超过十五个字符').css({color : '#CE0421'});
		$('#_user_name_2_mark').html('<img src="/images/error_icon.png" alt="" />');
		return false;
	}else if(reg.test(username)==true){
	    $("#accounts").html('账号不能为纯数字').css({color : '#CE0421'});
	    $('#_user_name_2_mark').html('<img src="/images/error_icon.png" alt="" />');
	    return false;
	}else{
	    $("#accounts").html('由3-15个字符组成').css({color : '#555555'});
		$('#_user_name_2_mark').html('<img src="/images/right_icon.png" alt="" />');
	}

	if(username=='')
	{
		$("#accounts").html('账号由3-15个字符组成').css({color : '#CE0421'});
		$('#_user_name_2_mark').html('<img src="/images/error_icon.png" alt="" />');
		return false;
	}else{
		$.ajax({
   			 type: "POST",
  			 url: "/check_user.php",
  			 data: "user_name="+username,
  			 success: function(msg){
	    		if(msg=="no"){
	    			$("#accounts").html('账号不能为纯数字').css({color : '#CE0421'});
	    			$('#_user_name_2_mark').html('<img src="/images/error_icon.png" alt="" />');
	    			return false;
	    		}else if(msg!=="" && msg!="no"){
	    			$("#accounts").html('账号已存在').css({color : '#CE0421'});
	    			$('#_user_name_2_mark').html('<img src="/images/error_icon.png" alt="" />');
	    			return false;
	    		}
    		}
		}); 
		return true;
	}
		
}

function testpass()
{
	var len = $("#user_password").val().length;
	min = 6;
	max = 16;
	if(len < min)
	{
		$("#pass").html('密码不能少于六位').css({color : '#CE0421'});
		$('#_pwd_mark').html('<img src="/images/error_icon.png" alt="" />');
		$("#q1").attr('class','');
		$("#q2").attr('class','');
		$("#q3").attr('class','');
		return false;
	}else if(len > max){
		$("#pass").html('密码不能超过十六位').css({color : '#CE0421'});
		$('#_pwd_mark').html('<img src="/images/error_icon.png" alt="" />');
		return false;
	} else {
		$("#pass").html('由6-16个字符(字母、数字、符号）组成，区分大小写').css({color : '#555555'});
		$('#_pwd_mark').html('<img src="/images/right_icon.png" alt="" />');
		if(len>=min)
		{
			$("#q1").attr('class','green');
		}

		if(len>=10)
		{
			$("#q2").attr('class','green');
		}else if(len<10){
			$("#q2").attr('class','');
		}

		if(len>=14)
		{
			$("#q3").attr('class','green');
		}else if(len<14){
			$("#q3").attr('class','');
		}
			
		}
		return true;
}

function confirmpass()
{
	var password = $("#user_password").val();
	var password_con = $("#re_user_password").val();
	if (password != password_con) {
		$("#pass2").html('两次密码请保持一致');
		$('#_repwd_mark').html('<img src="/images/error_icon.png" alt="" />');
		return false;
	}else if(password_con==''){
		$("#pass2").html('请填写确认密码');
		$('#_repwd_mark').html('<img src="/images/error_icon.png" alt="" />');
		return false;
	}else{
		$("#pass2").html('');
		$('#_repwd_mark').html('<img src="/images/right_icon.png" alt="" />');
		
	}
	return true;
}

function testemail()
{
	var email = $("#email_1").val();
 	var myreg = /^[a-zA-Z0-9_-]+([a-zA-Z0-9_-]|\.)+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{1,}){1,2})$/;

	if(!myreg.test(email)){
		$("#ema").html('请填写有效的邮件地址，公司请填写官方邮箱').css({color : '#CE0421'});
		$('#_email_mark').html('<img src="/images/error_icon.png" alt="" />');
		 return false;
	}else{
	
			$.ajax({
	   			 type: "post",
	  			 url: "/check_email.php",
	  			 data: "email="+email,
	  			 success: function(msg){
	  			 	if(msg==0){
	  			 		$("#ema").html('<span style="color : #555555">请填写有效的邮件地址，公司请填写官方邮箱</span>');
						$('#_email_mark').html('<img src="/images/right_icon.png" alt="" />');
						email_key = true;
	  			 	}else if(msg==1){
	  			 		$("#ema").html('邮箱已存在').css({color : '#CE0421'});
						$('#_email_mark').html('<img src="/images/error_icon.png" alt="" />');
					    email_key = false;
	  			 	}
		    	}
			}); 
	}

}

function authcode()
{
	var code = $("#com5").val();
	if(code==''){
		$('#_checkcode').html('<img src="/images/error_icon.png" alt="" />');	
		authcode_key = false;
	}
	$.ajax({
   			 type: "GET",
  			 url: "/check_verify.php?do=secc",
  			 data: "secc="+code,
  			 success: function(msg){
  			 	if(code!='')
  			 	{
		    		if(msg=='0')
		    		{
		    			//$("#code_show").html('验证码不正确').css({color : '#CE0421'});
		    			$('#_checkcode').html('<img src="/images/error_icon.png" alt="" />');	
		    			authcode_key = false;
		    		}else if(msg =='1'){
		    			$("#code_show").html('');
		    			$('#_checkcode').html('<img src="/images/right_icon.png" alt="" />');
		    			authcode_key = true;
		    		}
		    	}
	    	}
		}); 
	
}

function check_submit()
{
	var user_name = $("#user_name_in").val();
	var user_password = $("#user_password").val();
	var re_user_password = $("#re_user_password").val();
	var email = $("#email_1").val();
	var code = $("#com5").val();
	if ( !$("#agreement")[0].checked ) {
        alert('请先阅读协议');
        return false;
    }else if(user_name =='' && user_password==''){
		$("#accounts").html('账号由3-15个字符组成').css({color : '#CE0421'});
		$('#_user_name_2_mark').html('<img src="/images/error_icon.png" alt="" />');
		$("#pass").html('由6-16个字符(字母、数字、符号）组成，区分大小写').css({color : '#CE0421'});
		$('#_pwd_mark').html('<img src="/images/error_icon.png" alt="" />');
		return false;
	} else if(user_password=='') {
		$("#pass").html('请填写密码').css({color : '#CE0421'});
		$('#_pwd_mark').html('<img src="/images/error_icon.png" alt="" />');
		return false;
		
	} else if(re_user_password=='') {
		$("#pass2").html('请填写确认密码').css({color : '#CE0421'});
		$('#_repwd_mark').html('<img src="/images/error_icon.png" alt="" />');
		return false;
		
	} else if(email=='') {
		$("#ema").html('请填写有效的邮件地址，公司请填写官方邮箱').css({color : '#CE0421'});
		$('#_email_mark').html('<img src="/images/error_icon.png" alt="" />');
		return false;
		
	}else if(code ==''){
		//$("#code_show").html('验证码不能为空').css({color : '#CE0421'});
		$('#_checkcode').html('<img src="/images/error_icon.png" alt="" />');
	} else { 

		if(accounts()==true && testpass()==true && confirmpass()==true && email_key==true && authcode_key==true){
			$.ajax({
				type: 'POST',
				url: "/register_to.php",
				data: "user_name="+user_name+"&user_password="+user_password+"&email="+email+"&checkcode="+code,
					dataType: 'json',
					success: function(data){
						if(data=="no"){
							$("#accounts").html('账号不能为纯数字').css({color : '#CE0421'});
	    					$('#_user_name_2_mark').html('<img src="/images/error_icon.png" alt="" />');
	    					return false;
						}else if(data=="error_code"){
							$("#accounts").html('存在敏感字符').css({color : '#CE0421'});
							$('#_user_name_2_mark').html('<img src="/images/error_icon.png" alt="" />');
							return false;							
						}else{
	  			 			$("#register_dialog").css('display','none');
	  			 			$('#new_register').html(user_name).css({color : 'green'});
	  			 			register_ok('register_ok',user_name);
	  			 			window.setInterval(function(){window.location.href='http://www.anzhi.com/';}, 3000)
  			 			}
	    			}
				});
		}else{
			return false;
		}
	}

}

function show_register(id){
			$.ajax({
   			 type: "GET",
  			 url: "/show_register.php",
  			 data: "",
  			 success: function(msg){
  			 	if(msg.length > 50){
  			 			$('#'+id).html(msg);
  			 			showOpenNew(id);
  			 	}
    		}
		});
}


function register_ok(id,username){
		$.ajax({
   			 type: "GET",
  			 url: "/show_register.php",
  			 data: "show_id=1&username="+username,
  			 success: function(msg){
  			 	if(msg.length > 50){
  			 		$('#'+id).html(msg);
  			 		showOpenNew(id);
  			 	}
    		}
		}); 
}

function maintain(){
	$("#servicing").css('display','block');
	showOpenNew("servicing");
	return false;
}
/**搜索联想词
 * add by anbei 2014/12/01
 * searchTxt:搜索文本框
 * searchPop:搜索下拉列表框
 **/
var history_query = '';
function autoComplete(searchTxt,searchPop){
    var search_pop=$(searchPop),//搜索列表下拉框
        search_txt=$(searchTxt),//搜索框
        index=-1;//列表索引
    $(searchTxt).keyup(function(e){
		var e=e||event,
			currKey=e.keyCode||e.which||e.charCode;
		if(currKey==40 || currKey==38) return;
		
		var search_value = search_txt.val();
		search_value = ToCDB(search_value);
		search_value = search_value.replace(/^ +/, '', search_value);
		search_value = search_value.replace(/ +$/, '', search_value);
		//search_value = search_value.replace(/[\\\[\]\{\}\(\)\?\$\+\|\!\^\*@#%&__":><~/\.;'=-`]/g, '');
		search_value = search_value.replace(/[^\u4e00-\u9fa5a-z0-9]/gi,"");
		
		if (search_value == ''){
			$(searchPop).hide();
			return;
		}
		if (history_query == search_value) {
			$(searchPop).show();
			return;
		}
		history_query = search_value;
		var reg = new RegExp("(" + search_value + ")", 'i');
		var t = new Date().getTime();
		$(searchPop).hide();
		$.ajax({
			type: "POST",
			url: '/suggest.php?r=' + Math.random(),
			data: {'k':search_value,'t':t},
			dataType: "json",
			success: function(json){
				if (json && json['k'] == history_query) {
					var now = new Date().getTime();
					if (now - parseInt(json['t']) > 3000) return;
				
					var i, html='';
					var data = json['DATA'];
					for(i in data) {
						switch (data[i]['TYPE']) {
							case 2:
								var text = data[i]['DATA'].replace(reg, "<strong>$1</strong>");
								html += '<li><a href="/search.php?keyword='+data[i]['DATA']+'\" class=\"s_item\">'+text+'</a></li>';
							break;
							
							case 1:
								var text = data[i]['DATA']['softname'].replace(reg, "<strong>$1</strong>");
								var star = -12 * parseInt(data[i]['DATA']['score']);
								var official = '';
								if (data[i]['DATA']['official'] == 1){
									official = '<span class="official"></span>';
								}
								var download_url = '';
								if (data[i]['DATA']['download_url'] != ''){
									download_url = '<a href="'+data[i]['DATA']['download_url']+'" class="download">下载</a>';
								}
								html += '<li><div class="s_app cl" onclick="window.location.href=\''+data[i]['DATA']['url']+'\'"><img class="s_app_icon" src="'+data[i]['DATA']['iconurl']+'" alt=""/><div class="s_app_info"><h4>'+text+'</h4><p class="mtop_b cl"><span class="star" style="background-position:0 '+star+'px;"></span>'+official+'</p><p><em>'+data[i]['DATA']['filesize']+'</em></p></div>'+download_url+'</div></li>';
							break;
						}
					}
					search_pop.html(html);
					search_pop.show();
					var li_lists=search_pop.find("li");//列表集合
						
					li_lists.each(function(){
						$(this).hover(function(){
							$(this).addClass("active").siblings().removeClass("active");
						},function(){
							$(this).removeClass("active");
						})
					});
					index=-1;
				}
			}
			//async: false
		});
    }).blur(function(){
		setTimeout(function(){
			$(searchPop).hide();
			index=-1;
		},200);
	}).keydown(function(e){
		var li_lists=search_pop.find("li");//列表集合
		var li_len=li_lists.length;//获取列表数量
		if(li_len>0){
			var e=e||event,
				currKey=e.keyCode||e.which||e.charCode;

			if(currKey==40 || currKey==38){
				if(currKey==38){//向上键
					if(index==-1){
						index = li_len-1;
					}else{
						index--;
						if (index < 0) {
							index = li_len-1;
						}
					}
				}else if(currKey==40){//向下键
					if(index==li_len-1){
						index=0;
					}else{
						index++;
						if (index==li_len){
							index=0;
						}
					}
				}
				var li_selelct = li_lists.eq(index);
				li_selelct.addClass("active").siblings().removeClass("active");
				if(index==0){
					$(this).val(li_selelct.find("h4").text());
				}else{
					$(this).val(li_selelct.find(".s_item").text());
				}
			}else if(currKey==13 && index!=-1 && li_len>0){
				if(index==0){
					$(this).val(li_lists.eq(index).find("h4").text());
				}else{
					$(this).val(li_lists.eq(index).find(".s_item").text());
				}
			}
		}
	});
}

function ToCDB(str) 
{ 
	var tmp = ""; 
	for(var i=0;i<str.length;i++) 
	{ 
		if(str.charCodeAt(i)>65248&&str.charCodeAt(i)<65375) 
		{ 
			tmp += String.fromCharCode(str.charCodeAt(i)-65248); 
		} 
		else 
		{ 
			tmp += String.fromCharCode(str.charCodeAt(i)); 
		} 
	} 
	return tmp 
}