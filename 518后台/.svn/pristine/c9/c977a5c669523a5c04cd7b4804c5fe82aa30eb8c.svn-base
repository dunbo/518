<?php 
/* A,B,C,D,E,F代表排名指标；a,b,c,d,e,f代表该指标所占权重
A=下载，B=好评度，C=评论条数，D=留存率，E=安装率，F=增速
下载=期间该产品客户端下载量/期间最高客户端下载量
好评度=期间该产品评论分数/期间最高评论分数
评论条数=期间该产品评论条数/期间最高评论条数
留存率=（期间该产品安装量-期间安装用户卸载量）/期间该产品安装量
安装率=期间该产品安装量/期间该产品下载量
增速=（期间该产品最后一天下载量-期间该产品第一天下载量）/期间该产品最后一天下载量【若增速值为负数，取0】
1、日排行【后台随机取近3个月某几天数据进行排位】（这样每天出现的产品都会有变化）
分别取下载量100-2000，100-3000产品测试
排行分数=A*a+B*b+C*c+D*d+E*e
初步设定a=30,b=5,c=5,d=20,e=30,f=10
2、周排行（取近1周数据）
排行分数=A*a+B*b+C*c+D*d+E*e
初步设定a=30,b=5,c=5,d=20,e=30,f=10
3、月排行（1个月的数据）
分数=客户端下载量
最热=周排行
备选库（10天内新上线或发新版产品）
分数=（期间该产品最后一天下载量-期间该产品新版或新上线第一天下载量）/期间该产品最后一天下载量/上线天数 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<script src="/Public/js/jquery.js"></script>
<script src="/Public/js/Base.js" type="text/javascript"></script>
<script src="/Public/js/Form/CheckForm.js" type="text/javascript"></script>
<link title="win2k-cold-1" href="/Public/js/calendar-win2k-cold-1.css" media="all" type="text/css" rel="stylesheet">
<script src="/Public/js/calendar_bak.js" type="text/javascript"></script>
<script src="/Public/js/calendar-zh.js" type="text/javascript"></script>
<script src="/Public/js/calendar-setup.js" type="text/javascript"></script>
<script src="/Public/js/result.js" type="text/javascript"></script>
<script src="Public/js/chartjs/highcharts.js" type="text/javascript"></script>
<style type="text/css">
#openBox{ width:700px; height:400px; border:1px #000000 solid; padding:20px; background:#FFFFFF; position:absolute; z-index:2;}
#openBox #close{ position:absolute; top:2px; right:2px; width:30px; height:24px; z-index:99999;}
#openBox #close a{display:block; width:30px; height:24px; font-size:12px;}
#openBox #shuju{width:700px; height:400px;}
#mark{ top:0; left:0; position:absolute; z-index:1; background:#000000; filter:alpha(opacity=50); opacity:0.5;}
</style>
<script>
$(document).ready(function(){
	$('#daily').show();
	$('#weekey').hide();
	$('#month').hide();
	$('#download').hide();
	 show_table(1);
});
function show(id){
 if(id == 1){
	$('#daily').show();
	$('#weekey').hide();
	$('#month').hide();
	$('#download').hide(); 
 }else if(id==2){
 	$('#daily').hide();
	$('#weekey').show();
	$('#month').hide();
	$('#download').hide(); 
 }else if(id==3){
 	$('#daily').hide();
	$('#weekey').hide();
	$('#month').show(); 
	$('#download').hide(); 
 }else if(id == 4){
  	$('#daily').hide();
	$('#weekey').hide();
	$('#month').hide(); 
	$('#download').show(); 
 }
 show_table(id);
}
function show_table(id){
	var id_arr = [1,2,3,4];
	for(i=0;i<id_arr.length;i++){
		if(id_arr[i]!=id){ $('#showtable'+id_arr[i]).hide();}
	}
	$('#showtable'+id).show();
}
function check_it(id){
 if($('#'+id).val()==''){
	alert("时间输入不能为空");
	return false;
 }
 return true;
}
function ranknum(id)
{
	if(id == 1 || id == 2 || id == 3){
		var mt1 = $('#mt1').val();
		var mt2 = $('#mt2').val();
		if(parseInt(mt1)>=parseInt(mt2)){
			alert("下载量范围不合理！！");
			return false;
		}
	}
	$('#submit_btn'+id).attr('disabled', true);
	$('#submit_btn'+id).attr('value', '数据正在处理中，请稍后....');
	var submit_url = '/softdata.php';
	if(id == 4){
		submit_url = '/softdata_download.php';
	}
	$.ajax({
		url:submit_url,
		type:'POST',
		data: $('#form_submit'+id).serialize(),
		timeout:1000000,
		success:function(data){
			alert('数据处理完成,点击确定更新');
			$('#showtable'+id).html("");
			$('#submit_btn'+id).attr('disabled', false);
			$('#submit_btn'+id).attr('value', '提交');
			$('#showtable'+id).html(data);
		}
	})
}
function showdata(id){
  $('#show'+id).attr('display','block');
}

function viewWidth(){
	return document.documentElement.clientWidth;
}
function viewHeight(){
	return document.documentElement.clientHeight;
}
function closeBox(){
	var oDiv = document.getElementById('openBox');
	var oMark  = document.getElementById('mark');
	document.body.removeChild(oDiv);
	document.body.removeChild(oMark);
}
function scrollY(){
	return document.documentElement.scrollTop || document.body.scrollTop;
}
function documentHeight(){
	return Math.max(document.documentElement.scrollHeight || document.body.scrollHeight,document.documentElement.clientHeight);
}
</script>
</head>
<center>
<table>
<tr><td>查询类别:</td><td><input type="radio" name="date_type" onclick="show(1)" checked=checked/>:日排行<input type="radio" name="date_type" onclick="show(2)" <?php if($_POST['act'] == 'weekey'){ echo "checked";}?>/>:周排行<input type="radio" name="date_type" onclick="show(3)"/>:月排行<input type="radio" name="date_type" onclick="show(4)" />:备选库</td><td><a target="_blank" href="/soft_score_rank.php">评分排行</a></td></tr>
</table>
<div id="daily">
<form id="form_submit1" action="" method="POST">
<table border=1>
<tr>
<td>日排行：
</td>
<td>
时间域：
<span style="border: 1px solid #7F9DB9; align: absmiddle; cursor: hand; display: inline-block; width: 124px; padding: 1px" id="WebCalendar1">
		<input type="text" size="15" value="<?php echo $_POST['fromdate'] ? $_POST['fromdate'] : date('Y-m-d',time()-3600*24*3);?>" style="cursor: hand; width: 100px; border: none 0px black;" name="fromdate" id="fromdate"><img align="absmiddle" width="16px" height="15px" style="margin: 1px; cursor: hand;" onclick="return showCalendar('fromdate', 'y-m-d');" src="/Public/js/calendar.gif"></span>
<span style="border: 1px solid rgb(127, 157, 185); padding: 1px; display: inline-block; width: 124px;" id="WebCalendar1">
		<input type="text" size="15" value="<?php echo $_POST['todate'] ? $_POST['todate'] :date('Y-m-d',time())?>" style="border: 0px none black; width: 100px;" name="todate" id="todate"><img align="absmiddle" width="16px" height="15px" style="margin: 1px;" onclick="return showCalendar('todate', 'y-m-d');" src="/Public/js/calendar.gif">
		</span>
</td>
<td>平均下载量<input type="text" id="mt1" name="mt1" value="100">-<input type="text" id="mt2" name="mt2" value="2000"></td>
<td><input type="hidden" name="act" value="daily"/> <input name="submit"  id="submit_btn1" value="提交" type="button" onclick="if(!check_it('time_area')){return false;} ranknum('1')"></td>
</tr>
<tr>
<td colspan="4">
<textarea name='gongshi'>
<?php echo $_POST['gongshi'] ? $_POST['gongshi'] : 'A*a+B*b+C*c+D*d+E*e+F*f';?>
</textarea>
a=<input type="text" size="5" name="a" value="<?php echo $_POST['a']?$_POST['a']:30;?>">
b=<input type="text" size="5" name="b" value="<?php echo $_POST['b']?$_POST['b']:5;?>">
c=<input type="text" size="5" name="c" value="<?php echo $_POST['c']?$_POST['c']:5;?>">
d=<input type="text" size="5" name="d" value="<?php echo $_POST['d']?$_POST['d']:20;?>">
e=<input type="text" size="5" name="e" value="<?php echo $_POST['e']?$_POST['e']:30;?>">
f=<input type="text" size="5" name="f" value="<?php echo $_POST['f']?$_POST['f']:10;?>">
A,B,C,D,E,F代表排名指标；a,b,c,d,e,f代表该指标所占权重
A=下载，B=好评度，C=评论条数，D=留存率，E=安装率，F=增速
下载=期间该产品客户端下载量/期间最高客户端下载量
好评度=期间该产品评论分数/期间最高评论分数
评论条数=期间该产品评论条数/期间最高评论条数
留存率=（期间该产品安装量-期间安装用户卸载量）/期间该产品安装量
安装率=期间该产品安装量/期间该产品下载量
增速=（期间该产品最后一天下载量-期间该产品第一天下载量）/期间该产品最后一天下载量【若增速值为负数，取0】
</td>
</tr>
</table>
</form>
</div>

<div id="weekey" style="display:none">
<form id="form_submit2">
<table border=1>
<tr>
<td>周排行：
</td>
<td>
时间域：
<span style="border: 1px solid #7F9DB9; align: absmiddle; cursor: hand; display: inline-block; width: 124px; padding: 1px" id="WebCalendar2">
		<input type="text" size="15" value="<?php echo $_POST['fromdate'] ? $_POST['fromdate'] : date('Y-m-d',time()-3600*24*7);?>" style="cursor: hand; width: 100px; border: none 0px black;" name="fromdate" id="fromdate1"><img align="absmiddle" width="16px" height="15px" style="margin: 1px; cursor: hand;" onclick="return showCalendar('fromdate1', 'y-m-d');" src="/Public/js/calendar.gif"></span>
<span style="border: 1px solid rgb(127, 157, 185); padding: 1px; display: inline-block; width: 124px;" id="WebCalendar2">
		<input type="text" size="15" value="<?php echo $_POST['todate'] ? $_POST['todate'] :date('Y-m-d',time())?>" style="border: 0px none black; width: 100px;" name="todate" id="todate1"><img align="absmiddle" width="16px" height="15px" style="margin: 1px;" onclick="return showCalendar('todate1', 'y-m-d');" src="/Public/js/calendar.gif">
		</span>
</td>
<td>平均下载量<input type="text" id="mt1" name="mt1" value="100">-<input type="text" id="mt2" name="mt2" value="2000"></td>
</td>
<td><input type="hidden" name="act" value="weekey"/>
<input type="hidden" name="rand" value="<?php echo rand()?>"/>
 <input name="submit"  id="submit_btn2" value="提交" type="button" onclick="ranknum('2')"></td>
</tr>
<tr>
<td colspan="4">
<textarea name='gongshi'>
A*a+B*b+C*c+D*d+E*e+F*f
</textarea>
a=<input type="text" size="5" name="a" value="30">
b=<input type="text" size="5" name="b" value="5">
c=<input type="text" size="5" name="c" value="5">
d=<input type="text" size="5" name="d" value="20">
e=<input type="text" size="5" name="e" value="30">
f=<input type="text" size="5" name="f" value="10">
A,B,C,D,E,F代表排名指标；a,b,c,d,e,f代表该指标所占权重
A=下载，B=好评度，C=评论条数，D=留存率，E=安装率，F=增速
下载=期间该产品客户端下载量/期间最高客户端下载量
好评度=期间该产品评论分数/期间最高评论分数
评论条数=期间该产品评论条数/期间最高评论条数
留存率=（期间该产品安装量-期间安装用户卸载量）/期间该产品安装量
安装率=期间该产品安装量/期间该产品下载量
增速=（期间该产品最后一天下载量-期间该产品第一天下载量）/期间该产品最后一天下载量【若增速值为负数，取0】
</td>
</tr>
</table>
</form>
</div>
<div id="month">
<form id="form_submit3">
<table>
<td>当前日期：(提取当前一个月的下载量)</td><td>
<span style="border: 1px solid #7F9DB9; align: absmiddle; cursor: hand; display: inline-block; width: 124px; padding: 1px" id="WebCalendar3">
		<input type="text" size="15" value="<?php echo date('Y-m-d',time());?>" style="cursor: hand; width: 100px; border: none 0px black;" name="currdate" id="fromdate2"><img align="absmiddle" width="16px" height="15px" style="margin: 1px; cursor: hand;" onclick="return showCalendar('fromdate2', 'y-m-d');" src="/Public/js/calendar.gif"></span>
</td>
<td>下载量<input type="text" id="mt1" name="min" value="100">-<input type="text" id="mt2" name="max" value="2000"></td>
<td>
<input type="hidden" name="act" value="month">
<input type="button" name="" id="submit_btn3" value="提交" onclick="ranknum('3')">
</td>
</table>
</form>
</div>
<div id="download">
<form id = "form_submit4">
<table>
<tr>
<td>
时间域：
<span style="border: 1px solid #7F9DB9; align: absmiddle; cursor: hand; display: inline-block; width: 124px; padding: 1px" id="WebCalendar4">
		<input type="text" size="15" value="<?php echo $_POST['fromdate'] ? $_POST['fromdate'] : date('Y-m-d',time()-3600*24*7);?>" style="cursor: hand; width: 100px; border: none 0px black;" name="fromdate" id="fromdate3"><img align="absmiddle" width="16px" height="15px" style="margin: 1px; cursor: hand;" onclick="return showCalendar('fromdate3', 'y-m-d');" src="/Public/js/calendar.gif"></span>
<span style="border: 1px solid rgb(127, 157, 185); padding: 1px; display: inline-block; width: 124px;" id="WebCalendar4">
		<input type="text" size="15" value="<?php echo $_POST['todate'] ? $_POST['todate'] :date('Y-m-d',time())?>" style="border: 0px none black; width: 100px;" name="todate" id="todate3"><img align="absmiddle" width="16px" height="15px" style="margin: 1px;" onclick="return showCalendar('todate3', 'y-m-d');" src="/Public/js/calendar.gif">
		</span>
</td>
<td>
平均下载量范围
<input type="text" name="min_data" value="100">-<input type="text" name="max_data" value="2000">
</td>
<td>
<input type="button" name="btn"  id="submit_btn4"  value="提交" onclick="ranknum(4)" />
</td>
</tr>
</table>
</form>
<table>
</table>
</form>
</div>
<div id="showtable1"> 
</div>
<div id="showtable2"> 
</div>
<div id="showtable3"> 
</div>
<div id="showtable4">
</div>
