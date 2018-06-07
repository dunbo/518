/*
 * jQuery common V 1.0
 * 
 * Date : 2015-05-20 20:00:00
 */


/* 图片覆层  */
function showImg(This,Title){
	var ScrollTop = $(window).scrollTop();
	$('html').addClass('FloatHtml');
	$('<div id="FloatBack"><\/div>').appendTo($('body')).css({width:$(document).width(),height:$(document).height(),opacity:'0.8'});
	$('body').append('<div id="ImgBack"><em><\/em><center>'+Title+'</center><img src="'+This.src+'" /><\/div>');
	ChangePos();
	function ChangePos(){
		var h = $(window).height() - $('#ImgBack').height();
		var w = $(window).width() - $('#ImgBack').width();
		$('#ImgBack').css({top:h>0?h/3+ScrollTop:10+ScrollTop,left:w>0?w/3:10});
		$(window).scrollTop(ScrollTop);
	}
	$('#ImgBack em').click(function(){
		$('#FloatBack, #ImgBack').remove();
		$('html').removeClass('FloatHtml');
		$(window).scrollTop(ScrollTop).unbind('resize',ChangePos);
	});
}

/* 论坛版块优先级 */
function priority(P_id){
	$('#'+P_id+'>a').toggle();
	$('#'+P_id+'>input').toggle();
	
	var prioritytext = '编辑优先级';
	if(!$('#'+P_id+'>input').is(":hidden")){
		prioritytext = '取消优先级';
		$('#submid').show();
		$('#submid').attr('style','display:inline-block;');
	}else{
		$('#submid').hide();
	}
	$('#priorityid').text(prioritytext);
}

function clickConfirm(retVal){
	if(confirm(retVal))return true;
	else return false;
}

function replaceNum(Val){
	return Val.replace(/\D/g,'');
}

$(function(){
	$(".tr:odd").css("background", "#FFFCEA");
	$(".tr:odd").each(function(){
		$(this).hover(function(){
			$(this).css("background-color", "#FFE1FF");
		}, function(){
			$(this).css("background-color", "#FFFCEA");
		});
	});
	$(".tr:even").each(function(){
		$(this).hover(function(){
			$(this).css("background-color", "#FFE1FF");
		}, function(){
			$(this).css("background-color", "#fff");
		});
	}); 
	
	$("#checkid").click(function(){
		if($('#checkid').attr('checked')){
			$(".c_heck").attr("checked",true);
		}else{
			$(".c_heck").attr("checked",false);
		}
	});	
});