function setTab(m,n){
var tli=document.getElementById("menu"+m).getElementsByTagName("li");
var mli=document.getElementById("main"+m).getElementsByTagName("ul");
    for(i=0;i<tli.length;i++){
       tli[i].className=i==n?"hover":"";
       mli[i].style.display=i==n?"block":"none";
    }
}
var isIe=(document.all)?true:false;
//设置select的可见状态
function setSelectState(state)
{
var objl=document.getElementsByTagName('select');
for(var i=0;i<objl.length;i++)
{
objl[i].style.visibility=state;
}
}
function mousePosition(ev)
{
if(ev.pageX || ev.pageY)
{
return {x:ev.pageX, y:ev.pageY};
}
return {
x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,y:ev.clientY + document.body.scrollTop - document.body.clientTop
};
}
//弹出方法
function showMessageBox(wTitle,content,pos,wWidth)
{
closeWindow();
var bWidth=parseInt(document.documentElement.scrollWidth);
var bHeight=parseInt(document.documentElement.scrollHeight);
if(isIe){
setSelectState('hidden');}
var back=document.createElement("div");
back.id="back";
var styleStr="top:0px;left:0px;position:absolute;background:#666;width:"+bWidth+"px;height:"+bHeight+"px;";
styleStr+=(isIe)?"filter:alpha(opacity=0);":"opacity:0;";
back.style.cssText=styleStr;
document.body.appendChild(back);
showBackground(back,50);
var mesW=document.createElement("div");
mesW.id="mesWindow";
mesW.className="mesWindow";
mesW.innerHTML="<div class='mesWindowTop'><table width='100%' height='100%'><tr><td>"+wTitle+"</td><td style='width:1px;'><input type='button' onclick='closeWindow();' title='关闭窗口' class='close' value='关闭' /></td></tr></table></div><div class='mesWindowContent' id='mesWindowContent'>"+content+"</div><div class='mesWindowBottom'></div>";
styleStr="left:"+(((pos.x-wWidth)>0)?(pos.x-wWidth):pos.x)+"px;top:"+(pos.y)+"px;position:absolute;width:"+wWidth+"px;";
mesW.style.cssText=styleStr;
document.body.appendChild(mesW);
}
//让背景渐渐变暗
function showBackground(obj,endInt)
{
if(isIe)
{
obj.filters.alpha.opacity+=1;
if(obj.filters.alpha.opacity<endInt)
{
setTimeout(function(){showBackground(obj,endInt)},5);
}
}else{
var al=parseFloat(obj.style.opacity);al+=0.01;
obj.style.opacity=al;
if(al<(endInt/100))
{setTimeout(function(){showBackground(obj,endInt)},5);}
}
}
//关闭窗口
function closeWindow()
{
if(document.getElementById('back')!=null)
{
document.getElementById('back').parentNode.removeChild(document.getElementById('back'));
}
if(document.getElementById('mesWindow')!=null)
{
document.getElementById('mesWindow').parentNode.removeChild(document.getElementById('mesWindow'));
}
if(isIe){
setSelectState('');}
}
//投票
function checkitshow(ev)
{
var objPos = mousePosition(ev);
messContent="<div style='padding:20px 0 20px 0;text-align:center' id='BIG'></div>";
showMessageBox('用户检测',messContent,objPos,350);
}
function friendshow(ev,uid,username)
{
var objPos = mousePosition(ev);
messContent="<div style='padding:20px 0 20px 0;text-align:center' z-index:1; id='BIG'><p>您确定要和用户：<span id ='username' class='cF00'>"+username+"</span>交换主页么?</p><input type='hidden' id='uid' name='uid' value='"+uid+"' /><input name='' type='button' value='交 换' class='Btn_bak' style='width:60px;' onclick='friend()'/>&nbsp;<input type='submit' name='button' id='button' value='取　消' class='Btn_bak' style='width:60px;' onclick='closeWindow()' />  </p></div>";
showMessageBox('印象投票',messContent,objPos,350);
}
function writemailshow(ev,uid,username)
{
var objPos = mousePosition(ev);
    messContent="<div style='padding:20px 0 20px 0;text-align:center'  z-index:1; margin-left:22px; id='BIG'><input type='hidden' id='uid' name='uid' value='"+uid+"' />标　题：<input type='text' name='title' id='mailtitle' style='width:170px;' class='Text_box1' maxlength='20' />限定20个字之内<br>内　容：<textarea name='text' id='mailcontent' class='Text_box1' style='width:280px; height:120px;margin-left:22px;' ></textarea><br/><br/>验证码:<input name='adminnum' type='text' class='Text_box1' id='theadminnum' style='width:80px; height:20px; text-align:center; line-height:20px;' /><img src='code.php' id='imgId' alt='看不清?请刷新' onClick=\"this.src=this.src+'?'\"/><br><br><center><input name='' type='button' value='发 信' class='Btn_bak' style='width:60px;' onclick='writemail()'/>&nbsp;<input type='submit' name='button' id='button' value='取　消' class='Btn_bak' style='width:60px;' onclick='closeWindow()' /></p></center></div>";
showMessageBox('印象投票',messContent,objPos,350);
}
//检测姓名_助理
function checkhelpername(thecheck) {
   var checkval=$("#"+thecheck+"").val();
   if(checkval!='')
   {
   $.post(''+APP+'/Helper/check',
         {c:thecheck,v:checkval,n:Math.random()},
            function (data)
            {
                 var thedata=eval("("+data+")");
                 if (thedata) {
                   $("#"+thecheck+"_span").html(thedata);
                 }
             }
         );
    }else {
            $("#"+thecheck+"_span").html('请输入');
    }
}
//检测姓名
function checkusername(thecheck) {
   var checkval=$("#"+thecheck+"").val();
   if(checkval!='')
   {
   $.post(''+APP+'/User/check',
         {c:thecheck,v:checkval,n:Math.random()},
            function (data)
            {
                 var thedata=eval("("+data+")");
                 if (thedata) {
                   $("#"+thecheck+"_span").html(thedata);
                 }
             }
         );
    }else {
            $("#"+thecheck+"_span").html('请输入');
    }
}
//循环显示城市
function selectcity(theval,thetype) {
   document.getElementById(''+thetype+'').length=1;
   var placeval=$("#"+theval+"").val();
   var targetSelNode = document.getElementById(thetype);
   var n=0;
   if(theval!='')
   {
   $.post(''+APP+'/User/selectplace',
         {v:placeval,n:Math.random()},
            function (data)
            {
                 var thedata=eval("("+data+")");
                 if (thedata) {
                    for (var o in thedata) {
                        targetSelNode.appendChild(createOption(o, thedata[o])); //在目标列表追加新的选项
                        n++;
                        if (n==thedata.length) {
                                break;
                        }
                    }
                 }
             }
         );
    }
}
//创建下拉菜单_城市
function createOption(value, text) {
     var opt = document.createElement("option");                        //创建一个option节点
     opt.setAttribute("value", text['place_id']);                                  //设置value
     opt.appendChild(document.createTextNode(text['placename']));                    //给节点加入文本信息
     return opt;
}
//循环显示行业
function selecttrade(theval,thetype) {

   document.getElementById(''+thetype+'').length=1;
   var placeval=$("#"+theval+"").val();
   var targetSelNode = document.getElementById(thetype);
   var n=0;
   if(theval!='')
   {
   $.post(''+APP+'/User/selecttrade',
         {v:placeval,n:Math.random()},
            function (data)
            {
                 var thedata=eval("("+data+")");
                 if (thedata) {
                    for (var o in thedata) {
                        targetSelNode.appendChild(createOptiontrade(o, thedata[o]));
                        n++;
                        if (n==thedata.length) {
                                break;
                        }
                    }
                 }
             }
         );
    }
}
//助理循环显示城市
function selectthetrade(theval,thetype) {

   document.getElementById(''+thetype+'').length=1;
   var placeval=$("#"+theval+"").val();
   var targetSelNode = document.getElementById(thetype);
   var n=0;
   if(theval!='')
   {
   $.post(''+APP+'/Helper/selecttrade',
         {v:placeval,n:Math.random()},
            function (data)
            {
                 var thedata=eval("("+data+")");
                 if (thedata) {
                    for (var o in thedata) {
                        targetSelNode.appendChild(createOptiontrade(o, thedata[o]));
                        n++;
                        if (n==thedata.length) {
                                break;
                        }
                    }
                 }
             }
         );
    }
}
//创建下拉菜单_行业
function createOptiontrade(value, text) {
     var opt = document.createElement("option");
     opt.setAttribute("value",text['trade_id']);
     opt.appendChild(document.createTextNode(text['tradename']));
     return opt;
}
//检查图片格式
function onUploadImgChange(sender)
{

    if(!sender.value.match(/.jpg|.gif|.png|.jpeg/i ) ){
        alert('图片格式无效');
        return false;
    }
}
function onUploadAPK(sender)
{

    if(!sender.value.match(/.apk/i ) ){
        alert('apk格式无效');
        return false;
    }
}

function tip_show(id){

	if($('#'+id).css('display') == 'none'){
		r=id.substring(4);
		$('#'+r).attr('checked',true);
		$('#'+id).css('display','block');
	}else{
		$('#'+id).css('display','none');
	}	
}
//设置房间数
function roomsubmit(url){
	var room_total = $('#room_total').val();
	var room_type = $('#room_type').val();
	if(room_total>0 && room_total<=10){
		if(confirm('您确定要重新设置房间数')){
			$.ajax({
				url: url+'update_room/room_total/'+room_total+'/room_type/'+room_type,
				type: 'post',
				dataType: 'json',
				success: function(ret) {
					if(ret['code']==1) {	//成功
						//alert(1);
						//alert(ret['msg']);
						location.reload();//加载页面
						//return false;
					}else{
						//alert(2);
						alert(ret['msg']);
						//return false;
					}
				}
			});
		}
	}else{
		alert('请设置正确的房间数');
	}
}

function reject_check_public(){
//	var reject_reason = arguments[0] ? arguments[0] : 'reject_reason'; 
	var rt = '';
	var mark_type = false;
	var start_choose = true;
	var last;
	var error = 0;
	var f_id = '' ;
	$('input:checkbox[name="reject[]"]').each(function(){
		if($(this).attr('checked') == 'checked'){
			if(!mark_type){
				if( $(this).attr('mark') == 1){
					f_id = 'f_'+$(this).parent().parent().attr('id');
					var f_content = $("#"+f_id).html();
					rt = rt.substring(0,rt.length-1);
					if(start_choose){
						rt += f_content+'('+$.trim($(this).val());
					}else{
						rt += '；<br />'+f_content+'('+$.trim($(this).val());
					}
					
					mark_type = true;
				}else{
					if(start_choose){
						rt +=  $(this).val()+'；';
						start_choose = false;
					}else{
						var bd_str = '';
						bd_str = rt.substring(rt.length-1,rt.length);
						if(bd_str ==  '；'){
							rt = rt.substring(0,rt.length-1);
						}
						rt += '；<br />' + $(this).val();					
					}				
					mark_type = false;
				}			
			}else{
				if( $(this).attr('mark') == 1){
					var f_id_2 = 'f_'+$(this).parent().parent().attr('id');
					if(f_id != f_id_2){
						f_id = f_id_2;
						var f_content_2 = $("#"+f_id_2).html();
						rt += ')；<br />'+ f_content_2 +'('+$.trim($(this).val());
					}else{
						rt += '，' + $.trim($(this).val());
					}					
					mark_type = true;
				}else{				
					rt += ')；<br />'+ $(this).val() + '；';
					mark_type = false;
				}
			}
		}
	});
	if(rt){
		if(mark_type){
			if(rt) rt += ')；<br />' ;
		}else{
			if(rt) rt += '<br />' ;
		}		
	}
	if($('#reject_reason').val()!='请输入其他驳回原因：' && $('#reject_reason').val()!='') {
		rt += $('#reject_reason').val() + '；<br />';
	}
	rtt = encodeURIComponent(rt); //把字符串作为 URI 组件进行编码。
	return rtt;
}