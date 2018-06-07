<script src="/Public/js/jquery.js"></script>
<script>
$(document).ready(function(){
	reState('left');
	reState('right');
});

function reState(perfix)
{
	$.ajax({
		url:'/top_test_action.php',
		type:'POST',
		data:$('#'+perfix+'_form').serialize(),
		success:function(data){
			$('#'+perfix).html(data);
		}
	})
}

function updateTopOrderNum()
{
	$('#formula_btn').attr('disabled', true);
	$('#formula_btn').attr('value', '数据正在更新，请稍后');
	$.ajax({
		url:'/top_test_action.php',
		type:'POST',
		data: $('#formula_form').serialize(),
		success:function(data){
			alert('数据更新完成，请重新查询');
			$('#formula_btn').attr('disabled', false);
			$('#formula_btn').attr('value', '提交');
			$('#'+perfix).html(data);
		}
	})
}
</script>
新排行算法
(安装-卸载)*60%+（安装/下载）*浏览量*30% + ((安装-卸载)*60%+（安装/下载）*浏览量*30%)*10%*评分*10% <br> <br> <br>
自定义算法公式: 可以使用的变量有 <br>
	安装 <br>
	下载 <br>
	卸载 <br>
	浏览量 <br>
	评分 <br>
<form id="formula_form">
<textarea id="formula" name="formula" cols="100">(安装-卸载)*60%+（安装/下载）*浏览量*30% + ((安装-卸载)*60%+（安装/下载）*浏览量*30%)*10%*评分*10% </textarea><br>
<input type="button" onclick="updateTopOrderNum()" value="提交" id="formula_btn"/><br>
</form>
<table border="1">
	<tr>
		<td>
			<form id="left_form">
			<select name="date">
				<option value='2011-08-15'>2011-08-15</option>
				<option value='2011-08-16'>2011-08-16</option>
				<option value='2011-08-17'>2011-08-17</option>
				<option value='2011-08-18'>2011-08-18</option>
				<option value='2011-08-19'>2011-08-19</option>
				<option value='2011-08-20'>2011-08-20</option>
				<option value='2011-08-21'>2011-08-21</option>
				<option value='2011-08-22'>2011-08-22</option>
			</select>
			<label><input type='radio' name="order" value='order_num' checked="true"/>按新算法</label>
			<label><input type='radio' name="order" value='download_cnt' />按当日下载</label>
			<input type="button" onclick="reState('left')" value="查询"/>
			</form>
		</td>
		<td>
			<form id="right_form">
			<select name="date">
				<option value='2011-08-15'>2011-08-15</option>
				<option value='2011-08-16'>2011-08-16</option>
				<option value='2011-08-17'>2011-08-17</option>
				<option value='2011-08-18'>2011-08-18</option>
				<option value='2011-08-19'>2011-08-19</option>
				<option value='2011-08-20'>2011-08-20</option>
				<option value='2011-08-21'>2011-08-21</option>
				<option value='2011-08-22'>2011-08-22</option>
			</select>
			<label><input type='radio' name="order" value='order_num' />按新算法</label>
			<label><input type='radio' name="order" value='download_cnt' checked="true"/>按当日下载</label>
			<input type="button" onclick="reState('right')" value="查询"/>
			</form>
		</td>
	</tr>
	<tr>
		<td id='left'></td>
		<td id='right'></td>
	</tr>
</table>