    var textarea = '100字以内，内容不能为空';
    input_focus('#reject_reason',textarea);
    input_focus('#remark_reason',textarea);
	function input_focus(obj,text){ //输入框提示文字
    	    if($.trim($(''+obj+'').val())==text) {
				$(''+obj+'').css({color:'#999999',fontSize:'14px'});
			} else {
				$(''+obj+'').css({color:'#000',fontSize:'14px'});
			}
			$(''+obj+'').live('focus',function(){
				if($.trim($(''+obj+'').val())==text){
					$(''+obj+'').val('');
					$(''+obj+'').css({color:'#000',fontSize:'14px'});
				}
			});
			$(''+obj+'').live('blur',function(){
				if($.trim($(''+obj+'').val())==''){
					$(this).val(text);
					$(''+obj+'').css({color:'#999999',fontSize:'14px'});
				}
			});
	}
     
	//查看备注信息
	function show_remark(id,str){
		if(!!str){
			$('#remark_reason').val(str);
		}else{
			$('#remark_reason').val(textarea);
		}
		$('#remark_id').val(id);
		$("#open_remark").zxxbox();
	}
	$('#cancel_remark').click(function(){
		$.zxxbox.hide();
	});
	function remark_sub(url){
		var remark_reason = '';
		$('#remark_sub').live('click',function(){
		remark_reason = $.trim($('#remark_reason').val());
		var id = $.trim($('#remark_id').val());
		if(remark_reason=='' || remark_reason ==textarea){
			alert('请填写正确的备注信息！');
			return false;
		}
		if(remark_reason.length>100){
			alert('不能超过100个字!');
			return false;
		}
		var data = '&id='+id+'&remark='+remark_reason;
		$.ajax({ 
			url: url,
			type: 'POST',
			data: data,
			dataType: 'json',
			success: function(data){
				$.zxxbox.hide();
				if(!!data && data.success){
					alert(data.msg);
					location.reload();//加载页面 
				}else{
					alert(data.msg);
					location.reload();//加载页面 
				}
			}
		});
	});
	}
	function open_debut_resource(id){	
		$("#debut_resource_"+id).zxxbox();
	}
	function getLocalTime(nS) {     
	   var da = nS*1000;
		da = new Date(da);
		var y = da.getFullYear()+'-';
		var m = da.getMonth()+1+'-';
		var d = da.getDate();
		//var h = da.getHours();　
		//var i = da.getMinutes();　
		//var s = da.getSeconds();　
		var dd = y+m+d;
		return dd; 
	}   
         
	function save_debut_time(id,tm,softname,pkg){ 
		$("#pkg_softname").html(softname+'('+pkg+')');
		$("#update_debut_tm").val(getLocalTime(tm));
		$("#debut_tm_div").zxxbox();
			$('#debut_submit').die();
			$('#debut_submit').live('click',function(){
				$.zxxbox.hide();
				var update_debut_tm = $("#update_debut_tm").val();
				$.ajax({
					url: '/index.php/Dev/ApplyDebut/save_debut_time',
					data: '&id='+id+'&update_debut_tm='+update_debut_tm,
					type: 'POST',
					dataType: 'json',
					success: function(data) {
						if(data.status == 0){
							alert(data.info);
							return false;
						}
						if(data.code == 0){
							alert(data.msg);
							return false;
						}else{
							gray_processing(data.msg);
						}
					}
				});
			});
	}	