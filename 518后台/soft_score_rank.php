<html>
<head>
<head>
<script src="/Public/js/jquery.js"></script>
<script src="/Public/js/Base.js" type="text/javascript"></script>
<script src="/Public/js/Form/CheckForm.js" type="text/javascript"></script>
<link title="win2k-cold-1" href="/Public/js/calendar-win2k-cold-1.css" media="all" type="text/css" rel="stylesheet">
<script src="/Public/js/calendar_bak.js" type="text/javascript"></script>
<script src="/Public/js/calendar-zh.js" type="text/javascript"></script>
<script src="/Public/js/calendar-setup.js" type="text/javascript"></script>
<script src="/Public/js/result.js" type="text/javascript"></script>
<script>
function ranknum()
{	
	if($('#mval').val() == ""){
	  alert("基数不能为空!!");
	  return false;
	}
	$('#submit_btn').attr('disabled', true);
	$('#submit_btn').attr('value', '数据正在处理中，请稍后....');
	$.ajax({
		url:'/rank_action.php',
		type:'POST',
		data: $('#form_data').serialize(),
		timeout:1000000,
		success:function(data){
			alert('数据处理完成,点击确定更新');
			$('#showtable').html("");
			$('#submit_btn').attr('disabled', false);
			$('#submit_btn').attr('value', '提交');
			$('#showtable').html(data);
		}
	})
}
</script>
</head>
<b>评论排行</b>
<form id="form_data">
<table border=1>
<tr><td>公式：</td><td><input type="text" name="gongshi" size="50" value=<?php echo $_POST['gongshi'] ? $_POST['gongshi'] : '(v/(v+m))*R+(m/(v+m))*C'?>>(v/(v+m))*R + (m/(v+m))*C</td></tr>
<tr><td>基数(m)</td><td><input type="text" id="mval" name="m" size="5" value="<?php echo $_POST['m'] ? $_POST['m'] : 10;?>"></td></tr>
<tr><td></td><td><input type="button" id="submit_btn" onclick="ranknum()" name="submit" value="提交"></td></tr>
</table>
</form>
<div id="showtable">

</div>
</html>