// JavaScript Document
$(document).ready(function(){
	var ulLength = $('#photo_flash ul li').length;
	var ulLiWidth = $('#photo_flash ul li').width();
	var index = 0;
	var index2;
	var timer = null;
	var btnLeft = $('#photo_left');
	var btnRight = $('#photo_right');
	$('#photo_flash ul').width(ulLiWidth*ulLength);
	var slider = function(){
		$('#photo_flash ul').animate(
			{left:-(ulLiWidth*index)+6}
		);
	}
	var setAuto = function(){
		clearInterval(timer);
		timer = setInterval(function(){
			if(index==ulLength-4)
			{
				index = 0;
			}
			else{
				index++;
			}
			slider();
			index = index;
			setBg();
		},5000);
	};
	var setBg = function(){
		if(index!=0)
		{
			btnLeft.css('backgroundImage','url(images/arrow_03.png)');
			}
			else{
				btnLeft.css('backgroundImage','none');
			}
			if(index==ulLength-4)
			{
				btnRight.css('backgroundImage','none');
			}
			else{
				btnRight.css('backgroundImage','url(images/arrow_01.png)');
			}
	};
	setAuto();
	$('#photo_right').bind('click',function(){
		clearInterval(timer);
		if(index==ulLength-4)
		{
			return;
		}
		else{
			index++;
		}
		slider();
		setTimeout(setAuto,5000);
		setBg();
	});
	$('#photo_left').bind('click',function(){
		clearInterval(timer);
		if(index==0)
		{
			return;
		}
		else{
			index--;
		}
		slider();
		setTimeout(setAuto,5000);
		setBg();
	});
	$('#photo_left').bind('mouseover',function(){
		$(this).css('backgroundImage','url(images/arrow_02.png)');
		if(index==0){
			$(this).css('backgroundImage','none');
		}
	});
	$('#photo_left').bind('mouseout',function(){
		$(this).css('backgroundImage','url(images/arrow_03.png)');
		if(index==0){
			$(this).css('backgroundImage','none');
		}
	});
	$('#photo_right').bind('mouseover',function(){
		$(this).css('backgroundImage','url(images/arrow_04.png)');
		if(index==ulLength-4){
			$(this).css('backgroundImage','none');
		}else{
			
		}
	});
	$('#photo_right').bind('mouseout',function(){
		$(this).css('backgroundImage','url(images/arrow_01.png)');
		if(index==ulLength-4){
			$(this).css('backgroundImage','none');
		}
	});
});