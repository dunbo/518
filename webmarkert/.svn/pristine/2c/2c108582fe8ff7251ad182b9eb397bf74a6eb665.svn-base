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
	$('.recommend li').bind({mouseover:function(){
		$(this).css('backgroundColor','#eff1f3');
	},
	mouseout:function(){
		$(this).css('backgroundColor','');
	}
	});
	$('.recommend li').bind({mouseover:function(){
		$(this).children('.stars').css('display','none');
		$(this).children('.down').css('display','block');
	},
	mouseout:function(){
		$(this).children('.stars').css('display','block');
		$(this).children('.down').css('display','none');
	}
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
	$('#hotlist1 li:even').css('backgroundColor','#f4faf9');
	$('#hotlist2 li:even').css('backgroundColor','#f4faf9');
	$('#bj_recommend1 li:odd').css('backgroundColor','#f4faf9');
	$('#bj_recommend2 li:odd').css('backgroundColor','#f4faf9');
	
	$('#online_games li:last').css('border','none');
	$('#bj_recommend1 li:last').css('border','none');
	$('#bj_recommend2 li:last').css('border','none');
	$('#last_game li:last').css('border','none');
	$('#last_app li:last').css('border','none');
	$('#hotlist1 li:last').css('border','none');
	$('#hotlist2 li:last').css('border','none');
	$('#app_hot li:even').css('backgroundColor','#f4faf9');
	$('#app_hot li:last').css('border','none');
	$('#paihang1 li:even').css('backgroundColor','#f4faf9');
	$('#paihang2 li:even').css('backgroundColor','#f4faf9');
	$('#paihang3 li:even').css('backgroundColor','#f4faf9');
	$('#paihang1 li:last').css('border','none');
	$('#paihang2 li:last').css('border','none');
	$('#paihang3 li:last').css('border','none');
	$('#item_game li:even').css('backgroundColor','#f4faf9');
	$('#item_game li:last').css('border','none');
	

});

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
	
	$('#hotlist1 li:even').css('backgroundColor','#f4faf9');
	$('#hotlist2 li:even').css('backgroundColor','#f4faf9');
	$('#bj_recommend1 li:odd').css('backgroundColor','#f4faf9');
	$('#bj_recommend2 li:odd').css('backgroundColor','#f4faf9');
	
	$('#online_games li:last').css('border','none');
	$('#bj_recommend1 li:last').css('border','none');
	$('#bj_recommend2 li:last').css('border','none');
	$('#last_game li:last').css('border','none');
	$('#last_app li:last').css('border','none');
	$('#hotlist1 li:last').css('border','none');
	$('#hotlist2 li:last').css('border','none');
	$('#app_hot li:even').css('backgroundColor','#f4faf9');
	$('#app_hot li:last').css('border','none');
	$('#paihang1 li:even').css('backgroundColor','#f4faf9');
	$('#paihang2 li:even').css('backgroundColor','#f4faf9');
	$('#paihang3 li:even').css('backgroundColor','#f4faf9');
	$('#paihang1 li:last').css('border','none');
	$('#paihang2 li:last').css('border','none');
	$('#paihang3 li:last').css('border','none');
	$('#item_game li:even').css('backgroundColor','#f4faf9');
	$('#item_game li:last').css('border','none');
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
		oClose2.onclick = oClose.onclick = function(){
			document.body.removeChild(oMark);
			oDiv.style.display = 'none';
		};
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
	if(id==1)
	{
		$(obj).parent().css('backgroundPosition','0 0');
	}
	else{
		$(obj).parent().css('backgroundPosition','0 -28px');
	}

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
	if(id==1)
	{

		$(obj).parent().css('backgroundPosition','0 -84px');
	}
	else if(id==2)
	{
		$(obj).parent().css('backgroundPosition','0 -56px');
	}
	else{
		$(obj).parent().css('backgroundPosition','0 -112px');
	}
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
	$(obj).parent().children().removeClass('currentcolor');
	$(obj).addClass('currentcolor');
	$('.'+classname).css('display','none');
	$('#'+classname+id).css('display','block');
}

// var bindEvent4val = 0;
// var bindEvent4nav = 6;
// var bindEvent4Ajax = function () {
		
	// bindEvent4val++;
	
	// if (bindEvent4val == bindEvent4nav) {
		// rebind_obj(document);
	// }

// }