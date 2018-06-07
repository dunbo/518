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
	$('#bj_recommend3 li:odd').css('backgroundColor','#f4faf9');
	
	$('#online_games li:last').css('border','none');
	$('#bj_recommend1 li:last').css('border','none');
	$('#bj_recommend2 li:last').css('border','none');
	$('#bj_recommend3 li:last').css('border','none');
	$('#last_game li:last').css('border','none');
	$('#last_app li:last').css('border','none');
	$('#hotlist1 li:last').css('border','none');
	$('#hotlist2 li:last').css('border','none');
	$('#app_hot li:even').css('backgroundColor','#f4faf9');
	$('#app_hot li:last').css('border','none');
	$('#paihang1 li:even').css('backgroundColor','#f4faf9');
	$('#paihang2 li:even').css('backgroundColor','#f4faf9');
	$('#paihang1 li:last').css('border','none');
	$('#paihang2 li:last').css('border','none');
	$('#item_game li:even').css('backgroundColor','#f4faf9');
	$('#item_game li:last').css('border','none');
	$('#online_games li:even').css('backgroundColor','#f4faf9');
	$('#online_games li:last').css('border','none');

});

function sidetab(obj,id,classname){
	$('.'+classname).css('display','none');
	$('#'+classname+id).css('display','block');
	$('#'+classname+id).css('display','block');
	if(id==1)
	{
		$(obj).parent().css('backgroundPosition','0 0');
	}
	else{
		$(obj).parent().css('backgroundPosition','0 -28px');
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
			//iNow =0;
		}
		else{
			iNow++;
		}
		startMove(oUl,{'left':-iNow*aLi[0].offsetWidth});
		if(btnLeft)
		{
			if(iNow!=0)
			{
				btnLeft.style.backgroundImage = 'url(images/arrow_04.png)'
			}
			else{
				btnLeft.style.backgroundImage = 'url(images/arrow_08.png)'
			}
			if(iNow==aLi.length-3)
			{
				btnRight.style.backgroundImage = 'url(images/arrow_07.png)'
			}
			else{
				btnRight.style.backgroundImage = 'url(images/arrow_03.png)'
			}
		}
	}
	btnLeft.onclick = function(){
		if(iNow==0)
		{
			return;
			//iNow =aLi.length-1;
		}
		iNow--;
		startMove(oUl,{'left':-iNow*aLi[0].offsetWidth});
		if(iNow!=0)
			{
				btnLeft.style.backgroundImage = 'url(images/arrow_04.png)'
			}
			else{
				btnLeft.style.backgroundImage = 'url(images/arrow_08.png)'
			}
			if(iNow==aLi.length-3)
			{
				btnRight.style.backgroundImage = 'url(images/arrow_07.png)'
			}
			else{
				btnRight.style.backgroundImage = 'url(images/arrow_03.png)'
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
	oUl.innerHTML += oUl.innerHTML;
	oUl.style.width = aLi.length * aLi[0].offsetWidth + 'px';
	btnRight.onclick = function(){
		if(iNow ==aLi.length-3 ){
			iNow =parseInt((aLi.length-3)/2);
		}
		else{
			iNow++;
		}
		startMove(oUl,{'left':-(iNow*aLi[0].offsetWidth+80)});
	}
	btnLeft.onclick = function(){
		if(iNow==0)
		{
			iNow =parseInt(aLi.length/2)-1;
		}
		else{
		iNow--;
		}
		startMove(oUl,{'left':-(iNow*aLi[0].offsetWidth+80)});
	}
}

function tab(obj,id,classname){
	$(obj).parent().children().removeClass('currentcolor');
	$(obj).addClass('currentcolor');
	$('.'+classname).css('display','none');
	$('#'+classname+id).css('display','block');
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