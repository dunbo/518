var flag = false;
function selectAll(check_obj,this_obj) {	//全选
	
	var check_obj = $('input.'+check_obj+'');
	var this_obj  = $('input[name='+this_obj+']');
	if(!flag){
		check_obj.each(function(){
			$(this).attr('checked',true);
		});
		this_obj.each(function(){
			$(this).attr('checked',true);
		});
		flag = true;
		get_checked_data(check_obj,this_obj);
		return;
	}
	if(flag){
		check_obj.each(function(){
			$(this).attr('checked',false);
		});
		this_obj.each(function(){
			$(this).attr('checked',false);
		});
		flag = false;
		get_checked_data(check_obj,this_obj);
		return;
	}	
}
$("#oper_reason_other").live('click',function(){
	
	 if($(this).attr('checked')){
	 		 $("input[name='oper_reason[]']").eq(2).show();
	 	}else{
	 		 $("input[name='oper_reason[]']").eq(2).val('').hide();
	 	}
});
function get_checked_data(check_obj,this_obj){
	setTimeout(function(){
	var names = '';
	var package_num = [];
	$('input.sum_data').each(function(){
		if($(this).attr('checked')){
			names = $(this).attr('name');
			if(!!names){
				package_num.push(names);
			}
		}
	});
	if(ov4(package_num).length>1){
		
		alert('请选择同一个软件!');
		check_obj.each(function(){
			$(this).attr('checked',false);
		});
		this_obj.each(function(){
			$(this).attr('checked',false);
		});
		
		flag = false;
		return false;
	}
		var sum_data = 0,str = '';
		$("input[name='"+names+"']:checked").each(function(){
			str+= $(this).val()+'+';
			sum_data += Number($(this).val());
		});
		str = str.substring(0,str.length-1);
		if(!!str && !!sum_data){
			$('#sum_data').html('&nbsp&nbsp'+str+'='+sum_data); 
			$('#sum_data_bottom').html('&nbsp&nbsp'+str+'='+sum_data);
		}else{
			$('#sum_data').html('');
			$('#sum_data_bottom').html('');
		}

	},500);

}
function ov4(ar){
	var m=[],f;
	for(var i=0;i<ar.length;i++){
		f=true;
		for(var j=0;j<m.length;j++)
			if(ar[i]===m[j]){f=false;break;};
			if(f)m.push(ar[i])
    }
	return m.sort(function(a,b){return a-b});
}
$('input.sum_data').click(function(){
	var names = '';
	var package_num = [];
	$('input.sum_data').each(function(){
		if($(this).attr('checked')){
			names = $(this).attr('name');
			if(!!names){
				package_num.push(names);
			}
		}
	});
	if(ov4(package_num).length>1){
		alert('请选择同一个软件!');
		return false;
	}
	var sum_data = 0,str = '';
	$("input[name='"+names+"']:checked").each(function(){
		str+= $(this).val()+'+';
		sum_data += Number($(this).val());
	})
	str = str.substring(0,str.length-1);
	if(!!str && !!sum_data){
		$('#sum_data').html('&nbsp&nbsp'+str+'='+sum_data); 
		$('#sum_data_bottom').html('&nbsp&nbsp'+str+'='+sum_data); 
	}else{
		$('#sum_data').html('');
		$('#sum_data_bottom').html('');
	}
});
//计算增扣量
function count_number(is_count,max_day,sum_data,his_add_data,his_cut_data){
	        var multiple = $('#multiple').val();
			var num = sum_data*multiple;
			if(is_count==1){
						$('#multiple').change(function(){
				            multiple = $(this).val();
				            num = $('#total_sum').val()*multiple;
							var downloaded = Number(max_day)+Number(num)+Number(his_add_data)-Number(his_cut_data);
					        $('#soft_last_data').html(downloaded);//软件剩余量
					        $('input[name=soft_last_data]').val(num);
					        $('#count_num').html('&nbsp&nbsp'+num);
						});
						$('#total_sum').focusout(function(){
							multiple =  $('#multiple').val();
				            num = $(this).val()*multiple;
							var downloaded = Number(max_day)+Number(num)+Number(his_add_data)-Number(his_cut_data);
					        $('#soft_last_data').html(downloaded);//软件剩余量
					        $('input[name=soft_last_data]').val(num);
					        $('#count_num').html('&nbsp&nbsp'+num);
						});
						    //num = $('#total_sum').val()*multiple;
							var downloaded = Number(max_day)+Number(num)+Number(his_add_data)-Number(his_cut_data);
					        $('#soft_last_data').html(downloaded);//软件剩余量
					        $('input[name=soft_last_data]').val(num);
					        $('#count_num').html('&nbsp&nbsp'+num);
					}else{
						$('#multiple').change(function(){
				            multiple = $(this).val();
				            num = $('#total_sum').val()*multiple;
							var downloaded = Number(max_day)-Number(num)+Number(his_add_data)-Number(his_cut_data);
					        $('#soft_last_data').html(downloaded);//软件剩余量
					        $('input[name=soft_last_data]').val(num);
					        $('#count_num').html('&nbsp&nbsp'+num);
					    });
					   $('#total_sum').focusout(function(){
					   		 multiple =  $('#multiple').val();
				            num = $(this).val()*multiple;
							var downloaded = Number(max_day)-Number(num)+Number(his_add_data)-Number(his_cut_data);
					        $('#soft_last_data').html(downloaded);//软件剩余量
					        $('input[name=soft_last_data]').val(num);
					        $('#count_num').html('&nbsp&nbsp'+num);
					   });
					   //num = $('#total_sum').val()*multiple;
						var downloaded = Number(max_day)-Number(num)+Number(his_add_data)-Number(his_cut_data);
					        $('#soft_last_data').html(downloaded);//软件剩余量
					        $('input[name=soft_last_data]').val(num);
					        $('#count_num').html('&nbsp&nbsp'+num);

			}
	
}

$('#word_exp').click(function(){
   $("#word").zxxbox();

});
function show_log(package){
    $("#show_log").zxxbox();
    $('#log_package').val(package);
    $('#show_log_sub').click(function(){
        if($('#log_start_time').val()==''){
              alert('请选择开始时间！');
              return false;
        }
        if($('#log_end_time').val()==''){
              alert('请选择结束时间！');
              return false;
        }
        if($('#log_package').val()==''){
           alert('获取包名失败！');
           return false;
        };
       $('#show_log_from').submit();
        closes();
    });
}
$('#sub').click(function(){
	$('#brush_oper').submit();
});
//刷量操作提交检测
function form_chk() {
	if($('input[name=soft_last_data]').val()=='') {
			alert("获取软件剩余量失败！");
			return false;
	}
	var oper_reason = '';
	$("input[name='oper_reason[]']:checked").each(function(){
		oper_reason+=$(this).val();
	});
	if(oper_reason=='' && $('#oper_reason').val()=='') {
			alert("请选择或输入操作原因！");
			return false;
	}
	var soft_last_data = $.trim($('#soft_last_data').text());
	if(soft_last_data<0){
		alert("软件剩余量不能为负值！");
		return false;
	}
	return true;
}
//关闭弹出层
function closes(){
	$.zxxbox.hide();
	//window.location.reload();
}