//选中加背景底色
function open_color(id_str){
// 在每个逗号(,)处进行分解。
	//console.log(id_str);
	var id_arr = new Array();
	var id_arr = id_str.split(',');
	for(var i in id_arr) {
		var delid = '#tr'+id_arr[i];
		$(delid).css({background:'#F5B50D'});
	}
}
function remove_color(id_str){
	var id_arr = new Array();
	var id_arr = id_str.split(',');
	for(var i in id_arr) {
		var delid = '#tr'+id_arr[i];
		$(delid).removeAttr('style');
	}
}
//全选
var flag = false;
function selectAlls() {
	if(!flag){
		$("[name='id[]']").each(function(){
			$(this).attr('checked',true);
		});
		$("[name='id_all[]']").each(function(){
			$(this).attr('checked',true);
		});
		flag = true;
		return;
	}
	if(flag){
		$("[name='id[]']").each(function(){
			$(this).attr('checked',false);
		});
		$("[name='id_all[]']").each(function(){
			$(this).attr('checked',false);
		});
		flag = false;
		return;
	}	
}
//全选类别
function cid_selectAll(e) {	
	var chkobj =  document.getElementsByName("catid[]");
	var len = chkobj.length;
	var ids = '';
	if($(e).attr('checked') == 'checked'){
	   for(var i = 0; i < len; i++){
			chkobj[i].checked = true;
			ids+= chkobj[i].value+',';
	   }
	 $('#cateid').val(ids);
	}
	if($(e).attr('checked') != 'checked'){
	   for(var a = 0; a <len; a++){
		 chkobj[a].checked = false;
	   }
	}
}
//判断选中
function Selected_check(){
	var id_arr = new Array();
	var i = 0;
	$("[name='id[]']").each(function(){
		if($(this).is(':checked')) {
			id_arr[i] = $(this).val();
			i++;
		}
	});
	var id_str = id_arr.join(',');
	if(id_str=='') {
		alert('请选择要操作的对象！');
		return false;
	}
	return id_str;
}
//屏蔽
function shield_soft(act){
	id_str = Selected_check();
	if(id_str){
		tb_show('屏蔽','/index.php/Dev/Soft/'+act+'?id='+id_str+'&height=250&width=350',false);
	}
}
function shield_soft_tmp(act){
	id_str = Selected_check();
	if(id_str){
		tb_show('屏蔽','/index.php/Dev/SoftwareReview/'+act+'?id='+id_str+'&height=250&width=350',false);
	}
}
//变灰不可点击处理
function gray_processing(ret){
	for(var i in ret) {
		var delid = '#tr'+ret[i];
		var aid = '#tr'+ret[i]+' a';
		var iid = '#tr'+ret[i]+' input';
		var imgid = '#tr'+ret[i]+' img';
		var bid = '#tr'+ret[i]+' button';
		$(delid+' td').removeAttr('bgcolor');
		$(delid).css({color:'#bcbcbc',background:'#ececec'});
		$(aid).css('color', '#bcbcbc');
		$(aid).removeAttr('href');
		$(aid).removeAttr('onclick');
		$(iid).attr({disabled:'disabled', name:'disabled'});
		$(iid).attr('checked', false);
		$(imgid).removeAttr('onclick');
		$(bid).removeAttr('onclick');
	}
}
//搜索条件验证
function checkinput(){
//	if ($('#softname').val().length >0 && $('#softname').val().length < 2) {
//		alert('软件名称长度必须大于2');
//		return false;
//	}
//	if ($('#dev_name').val().length >0 && $('#dev_name').val().length < 2) {
//		alert('开发者名称长度必须大于2');
//		return false;
//	}
//	if ($('#content').val().length >0 && $('#content').val().length < 2) {
//		alert('内容长度必须大于2');
//		return false;
//	}	
	return true;	
} 
//角标状态
function note_type(){
	id_str = Selected_check();
	if(id_str){
		tb_show('角标状态','/index.php/Dev/Soft/note_type/id='+id_str+'&height=250&width=300',false);
	}
}
//zxxbox 关闭层
function zxxbox_hide(id){	
	if(id){
		remove_color(id);
	}
	$.zxxbox.hide();
}

//导出数据
function export_data(url){
	//进度条显示开始
	popup('await');
	$.ajax({
		'url': url,
		'type': 'get',
		'dataType': 'json',
		'success': function(res){
			if (res.type == 'pager') {
				//console.log(res.type);
				//console.log(res.url);
				//进行下一页请求
				return export_data(res.url);
			}else if (res.type == 'file') {
				//完成以后关闭进度条显示
				popclose('await');
				//进行文件下载
				location.href = res.url;
			}
		}
	});
}
//签名风险
function sign_show(softid){
    var sim_status = $('#sign_show_'+softid).css('display');
    $('.hide_soft').css('display','none');
    if (sim_status == 'none') {
        $('#sign_show_'+softid).css('display','block');
    } else {
        $('#sign_show_'+softid).css('display','none');
    } 
}

function get_dev_info(type){
	$("input[name='devinfo']").each(function(){
		var id = $(this).attr('id');
		if($(this).val()==''||$(this).val()=='0'){
			var package = $(this).attr('package');
			var param = {package:package};
			$.ajax({
				url: '/index.php/Dev/Soft/pub_dev_info',
				type: 'post',
				dataType:'json',
				data : param,
				async : true,
				success: function(msg){
					var str = "";
					var devid = msg.dev_id;
					if(msg.status == '0'){
						str += "<a target='_blank' href='/index.php/Dev/User/userlists/dev_id/"+devid+"'>";
					}else if(msg.status == '1'){
						str += "<a target='_blank' href='/index.php/Dev/User/auditforuser/dev_id/"+devid+"'>";
					}else if(msg.status == '-1'){
						str += "<a target='_blank' href='/index.php/Dev/User/reject_users/dev_id/"+devid+"'>";
					}else if(msg.status == '-2'){
						str += "<a target='_blank' href='/index.php/Dev/User/shield_users/dev_id/"+devid+"'>";
					}
					if(type==1){
						str += msg.dev_name+'</a><br/>'+devid+'<br/><p>';
					}else{
						str += msg.dev_name+'</a><br/><p>';
					}

					if(msg.type == '0'){
						str += '公司';
					}else{
						str += '个人';
					}
					str += '</p><br/>';
					str += "<a target='_blank' href='__URL__/global_search/type/1/email/"+msg.email+"'>";
					str += msg.email+'</a>';
					$("#"+id).parent().append(str);
				}
			});
		}
	})
}