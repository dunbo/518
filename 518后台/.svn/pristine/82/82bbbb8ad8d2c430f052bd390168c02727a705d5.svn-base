// JavaScript Document
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
		var oClose = null;
		var oClose2 = document.getElementById(closeId2);
		if(clolseId)
		{
			oClose = document.getElementById(clolseId);
				oClose2.onclick = oClose.onclick = function(){
				document.body.removeChild(oMark);
				oDiv.style.display = 'none';
			};
		}
		else{
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
function sidetab(obj,id,classname){
	$('.'+classname).css('display','none');
	$('#'+classname+id).css('display','block');
	$('#'+classname+id).css('display','block');
	$('.tab_title li').css('color','#595959');
	$('#tab_titleli'+id).css('color','#fff');
	if(id==1)
	{
		
		$(obj).parent().css('backgroundPosition','0 0');
	}
	else if(id==2)
	{
		$(obj).parent().css('backgroundPosition','0 -30px');
	}
	else{
		$(obj).parent().css('backgroundPosition','0 -205px');
	}

}
function sidetab3(obj,id,classname){
	if(id==3)
	{	
		window.location.href="/remind.php";
		
	}
	else if(id==0)
	{		
		window.location.href="/remind.php?read_status="+id;
	}
	else if(id==1){		
		window.location.href="/remind.php?read_status="+id;
	}

}

function showOrHideSend(obj,oDiv){
	var oDiv = document.getElementById(oDiv);
	if(oDiv.style.display == 'block')
	{	
		obj.style.background = 'url(images/icon_08.png) no-repeat 80px 14px';
		oDiv.style.display ='none';
	}
	else{
		obj.style.background =  'url(images/icon_09.png) no-repeat 80px 14px';
		oDiv.style.display ='block';
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

function addClass(obj){
	obj.className = 'currentstate';
}
function removeClass(obj){
	obj.className = '';
}
$(document).ready(function(){
	$('.submenu').parent().addClass('libackground');
	$('.mainmenu').each(function(index){
		if($(this).is(':has("ul")'))
		{
			$(this).hover(function(){
				$(this).children("ul").show()
				$(this).removeClass("libackground");
			},function(){
				$(this).children("ul").hide()
				$(this).addClass('libackground');
			})
		}
	});
	$('.submenu').each(function(index){
		$(this).hover(function(){
			$(this).parent().addClass("lihovercolor");
		},function(){
			$(this).parent().removeClass("lihovercolor");
		})
	}); 
	//左侧菜单
	$('#sieds_menu h4').each(function(index){
		$(this).bind('click',function(){
			$('#sieds_menu p').hide();
			$('#sieds_menu h4').addClass('hidebg');
			$(this).siblings().show();
			$(this).removeClass('hidebg');
		});
	});
	//点击input的时候字体颜色加深，值为空
	$('.inputtext').bind('focus',function(){
		$(this).css('color','#535353');
	});
	$('.textarea_style').bind('focus',function(){
		$(this).css('color','#535353');
	});
	$('.soft_list1_more').bind('click',function(){
		if($(this).siblings('.soft_list1_txt').height()==66)
		{
			$(this).siblings('.soft_list1_txt').height('auto');
			$(this).html('【收起】');
		}
		else{
			$(this).siblings('.soft_list1_txt').height(66);
			$(this).html('【更多】');
		}
	});
})

function showOpenNew(id){
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

function strtotime(day){
	re = /(\d{4})(?:-(\d{1,2})(?:-(\d{1,2}))?)?(?:\s+(\d{1,2}):(\d{1,2}):(\d{1,2}))?/.exec(day);
	return new Date(re[1],(re[2]||1)-1,re[3]||1,re[4]||0,re[5]||0,re[6]||0).getTime();
}
