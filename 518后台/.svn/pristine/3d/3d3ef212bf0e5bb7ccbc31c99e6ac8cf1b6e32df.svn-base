// JavaScript Document
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
	
	
function sidetab(obj,id,classname){
	$('.'+classname).css('display','none');
	$('#'+classname+id).css('display','block');
	$('.tab_title li').removeClass('current');
	$(obj).addClass('current');
}

function co_check_empty(v) {
	if (v.length == 0) {
		return false;
	}
	return true;
}

function co_check_name(v) {
	if(!/^[a-zA-Z0-9\u4e00-\u9fa5]{1,15}$/i.test(v)) {
		return false;
	}
	return true;
}

function co_check_IR(i,r){
	var i_value = r_value = 0;
	$('input[name="'+i+'"]:checked').each(function(){    
		i_value = Number(i_value) + Number($(this).val());    
	});
	$('input[name="'+r+'"]:checked').each(function(){    
		r_value = Number(r_value) + Number($(this).val());    
	});
	if ((i_value & r_value) != i_value) {
		return false;
	}
	$("#i_value").val(i_value);
	$("#r_value").val(r_value);
	return true;
}
