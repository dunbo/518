// JavaScript Document
function showOpenNew(id){
	var oDiv = document.getElementById(id);
	if(oDiv){
		oDiv.style.display = 'block';
		//oDiv.style.top = (viewHeight() - oDiv.offsetHeight)/2 + scrollY() + 'px';
		//oDiv.style.left = (viewWidth() - oDiv.offsetWidth)/2 + 'px';
	}
	//var oMark = document.createElement('div');
	//oMark.id = id;
	//oMark.innerHTML = '<iframe id="DivShim" scrolling="no" frameborder="0" src="" style="position:absolute; width:100%; height:100%; background-color:#ff0; border:none;" frameborder="no"></iframe>';
	//document.body.appendChild(oMark);
	//oMark.style.width = viewWidth() + 'px';
	//oMark.style.height = viewHeight() + 'px';
	window.onresize = window.onscroll = function(){
		//oMark.style.width = viewWidth() + 'px';
		//oMark.style.height = viewHeight() + 'px';
		var oDiv = document.getElementById(id);
		//if(oDiv){
			//oDiv.style.top = (viewHeight() - oDiv.offsetHeight)/2 + scrollY() + 'px';
			//oDiv.style.left = (viewWidth() - oDiv.offsetWidth)/2 + 'px';
		//}
	}
		
}
function closeBtn(id){
	var oMark = document.getElementById('closeBtn');
	var oDiv = document.getElementById(id);
			oDiv.style.display = 'none';
			$(oMark).remove();
			//document.body.removeChild(oMark);
	
	}
function dev_alert(msg,sec,url){//操作信息提示框
	$('#alert_div').remove();
	var str='';
	str+= '<div id="alert_div" class="newbox" style="display:block;top:76.5px;left:434.5px;min-height:60px;_height:60px"><div class="newbox_tips"><p>'+msg+'</p></div>';
	if(sec==0){
		str += '<div class="open_close_btn" style="text-align:center;">';
		str += '<a id="gosub" onclick="closeBtn(\'alert_div\');" href="javascript:;">关闭</a>';
		str += '</div>';
	}
	$('body').append(str);
    showOpenNew('alert_div');
    if(!!url){
    	if(url!='-1'){
    		setTimeout(function(){
    			top.location.href = ''+url+'';
    		},2000);
         
    	}else{
    		setTimeout(function(){
    			window.history.go(-1);
    		},2000);
        }
     }
     if(!!sec){
     	setTimeout(function(){
     		closeBtn('alert_div');
     		//window.location.reload();
		},(sec+1)*1000);
     }else{
        setTimeout(function(){
        	closeBtn('alert_div');
        	//window.location.reload();
		},2000);
	 }
}