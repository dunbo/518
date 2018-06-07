// JavaScript Document
$(document).ready(function(){
	$('.check_prev_shortbox').bind('mouseover',function(){
		$(this).parent().find('.check_prev_newbox').toggle();
		var oTop = $(this).height();
		$('.check_prev_newbox').css('top',oTop);
		$(this).parents().find('.check_prev_shortbox').bind('mouseout',function(){
			$(this).parent().find('.check_prev_newbox').css('display','none');
		});
	});
	$('#sieds_menu h4').each(function(index){
		$(this).bind('click',function(){
			if($(this).siblings().css('display')=='none')
			{
				$(this).siblings().css('display','block');
				$(this).find('span').removeClass('hidesub');
			}
			else
			{
				$(this).siblings().css('display','none');
				$(this).find('span').addClass('hidesub');
			}
			//$(this).removeClass('hidebg');
		});
	});
});
function searchmore(obj){
	if($(obj).parents('.search_table').siblings().css('display')=='none')
	{
		$(obj).parents('.search_table').siblings().css('display','block');
		$(obj).addClass('search_more_btn_up');
	}
	else{
		$(obj).parents('.search_table').siblings().css('display','none');
		$(obj).removeClass('search_more_btn_up');
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
function sidetab(obj,id,classname){
	$('.'+classname).css('display','none');
	$('#'+classname+id).css('display','block');
	$('.tab_title li').removeClass('current');
	$(obj).addClass('current');
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
	oMark.innerHTML = '<iframe id="DivShim" scrolling="no" frameborder="0" src="" style="position:absolute; top:0px; left:0px; width:100%; height:100%; background-color:#ff0; border:none;" frameborder="no"></iframe>';
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
