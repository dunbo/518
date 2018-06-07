<?php
ini_set("memory_limit", "2000M");
error_reporting(E_ERROR);
ini_set("display_errors", true);
set_time_limit(1000);
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
define('STATDB', 'slave');
$model = new GoModel();
$fromdate = dateform(trim($_POST['fromdate']));
$todate = dateform(trim($_POST['todate']));
$min_data = $_POST['min_data'];
$max_data = $_POST['max_data'];
$limit = 3;
$i = 0;
for($start = strtotime($fromdate);$start <= strtotime($todate);$start+=3600*24){
 $where_time_arr[] = $start;
}
$start = $where_time_arr[0];
$begin = 0;
$limit = 1000;
$arr_end = count($where_time_arr)-1;
$end = $where_time_arr[$arr_end];
$offset = "limit ".$begin.",".$limit;
$sql = "select package,submit_day,mob_dl_cnt,mob_up_cnt from sj_download_count 
 where submit_day between  $start and $end ";
//$sqllimit = $sql.$offset;
$source =  $model -> query($sql, STATDB);
$soft_arr = array();
//整合数据
while($result = $model -> fetch($source)){

	$soft_dl_cnt_arr[$result['package']] += $result['mob_dl_cnt'] + $result['mob_up_cnt'];
	$sum = $result['mob_dl_cnt'] + $result['mob_up_cnt'];
	$soft_dl_cnt_detail[$result['package']][] = array($result['submit_day'],$sum);

/* 	$begin++;
	$limit = $begin * $limit;
	$offset = "limit ".$begin.",".$limit;
	$sqllimit = $sql . $offset;
	$source =  $model -> query($sqllimit); */
}
$option = array('table' => 'sj_soft','where' => array('status' => 1,'hide' => 1),'field' => array('softid','package','softname'));
$softarr = $model -> findAll($option);

foreach($softarr as $info){
$pkg = $info['package'];
//if(empty($info['softname'])) continue;
$pkg_softname_map[$pkg] = $info['softname'];
$softid_array[$pkg] = $info['softid'];
$softname_arr[$info['softid']] = $info['softname'];
}
//var_dump(count($softarr),count($pkg_softname_map),count($softid_array),count($softname_arr));
//算出斜率并排序
$i = 0;
foreach($soft_dl_cnt_detail as $pkg => $info){
	$aver = $soft_dl_cnt_arr[$pkg]/count($where_time_arr); //平均值
	if($aver > $max_data || $aver < $min_data) continue;
	list($arfa_arr[$pkg],$arfa_arr_b[$pkg])= linear_regression($soft_dl_cnt_detail[$pkg]); //斜率
	$aver_arr[$pkg] = $aver;			//平均值
	foreach($soft_dl_cnt_detail[$pkg] as $info){
	  $download_date[$pkg][] = date('y/m/d',$info[0]);
	  $download_data[$pkg][] = intval($info[1]);
	  //($a * $x + $b)
	  $arfar_line[$pkg][] = $arfa_arr[$pkg]*$info[0]+$arfa_arr_b[$pkg];
	}
	$arr_len = count($download_data[$pkg]);
	$download_json_arr[$softid_array[$pkg]] = array(array('name'=>'下载量','data' =>$download_data[$pkg]),array('name'=>'线性趋势','data' =>$arfar_line[$pkg]));
    $download_json_date[$softid_array[$pkg]] = $download_date[$pkg];
}

arsort($arfa_arr);
echo "<table border=1>";
echo "<tr><td></td><td>软件名</td><td>包名</td><td>平均下载量</td><td>斜率</td></tr>";
$i = 0;
foreach($arfa_arr as $pkg => $arfa){
	if(!isset($pkg_softname_map[$pkg])) continue;
	$i++;
	echo "<tr><td>".$i."</td>
	<td>".$pkg_softname_map[$pkg]."</td>
	<td>".$pkg."</td>
	<td>".$aver_arr[$pkg]."</td>
	<td BGCOLOR='#00E3E3'><p onclick=\"showpanel(".$softid_array[$pkg].")\" style='cursor:pointer'>
	";
	echo sprintf("%01.5f", $arfa*100).'%';
	echo "</p></td></tr>";
}
echo "</table>";
$download_arr = json_encode($download_json_arr);
$download_date = json_encode($download_json_date);
$softname_arr = json_encode($softname_arr);
echo <<<EOF
<script>

var  download_arr = {$download_arr};
var  download_date = {$download_date};
var  softname_arr = {$softname_arr};
//showpanel();
function showpanel(id){
		//var stat_val = document.getElementById('softid_'+id).value;
		//var date_val = document.getElementById('date_'+id).value;
		var oDiv = document.createElement("div");
		var oMark = document.createElement("div");
		var divCode = '<div id="close"><a href="javascript:closeBox()">关闭</a></div><div id="shuju"></div>';
		oDiv.id = "openBox"
		oDiv.innerHTML = divCode;
		document.body.appendChild(oDiv);
		//alert(id);
		showPhoto('shuju',id);
		oDiv.style.left = (viewWidth() - oDiv.offsetWidth)/2 + 'px';
		oDiv.style.top = (viewHeight() - oDiv.offsetHeight)/2 + scrollY() + 'px';
		

		oMark.id = 'mark';
		document.body.appendChild(oMark);
		oMark.style.width = viewWidth() + 'px';
		oMark.style.height = documentHeight() + 'px';
	
		
		//关闭弹出框
			closeBox();
			function closeBox(){
				var oClose = document.getElementById('close');
				oClose.onclick = function(){
					document.body.removeChild(oDiv);
					document.body.removeChild(oMark);
				};
			}
			window.onresize = window.onscroll = function(){
			oMark.style.width = viewWidth() + 'px';
			oMark.style.height = documentHeight() + 'px';
			if(oDiv){
			oDiv.style.top = (viewHeight() - oDiv.offsetHeight)/2 + scrollY() + 'px';
			oDiv.style.left = (viewWidth() - oDiv.offsetWidth)/2 + 'px';
			}
		}
		
}
 
	
function showPhoto(box,id){
	   var chart;
	   var txt = softname_arr[id]+'下载量和线性趋势图';
	   var categories_data = download_date[id];
	   var data = download_arr[id];
	   //alert(data);
	   //alert(categories_data);
	   chart = new Highcharts.Chart({
		  chart: {
			 renderTo: box,
			 defaultSeriesType: 'line',
			 marginRight: 110,
			 marginBottom: 25
		  },
		  credits: false,
		  title: {
			 text: txt,
			 x: -20 //center
		  },
		  xAxis: {
			 categories:categories_data,
		  },
		  yAxis: {
			 title: {
				text: '下载量'
			 },
			 plotLines: [{
				value: 0,
				width: 1,
				color: '#808080'
			 }]
		  },
		  tooltip: {
			 formatter: function() {
				return '<b>'+ this.series.name +'</b><br/>'+ this.x +': '+ this.y;
			 }
		  },
		  legend: {
			 layout: 'vertical',
			 align: 'right',
			 verticalAlign: 'top',
			 x: 0,
			 y: 0,
			 borderWidth: 0
		  },
		  series:data
	   });
}


</script>
EOF;
function linear_regression($arr) {
	$n = 0;
	$sum_x = 0;
	$sum_y = 0;
	$sum_x_mult_y = 0;
	$sum_x_power_2 = 0;
	foreach ($arr as $coord) {
		$sum_x += $coord[0];
		$sum_y += $coord[1];
		$sum_x_mult_y += $coord[0] * $coord[1];
		$sum_x_power_2 += $coord[0] * $coord[0];
		$n += 1;
	}
	$avg_x = $sum_x / $n;
	$avg_y = $sum_y / $n;
	$a = ($sum_x_mult_y - $n * $avg_x * $avg_y) / ($sum_x_power_2 - $n * $avg_x * $avg_x);
	$b = $avg_y - $a * $avg_x;
	# y = a * x + b
	#return ($a * $x + $b);
    return array($a,$b);
}
function dateform($date){
	$arr = explode('-',$date);
	foreach($arr as $val){
	if(strlen($val)==1) $date_arr[] = '0'.$val;
	else $date_arr[] = $val;
	}
	$date = implode('-',$date_arr);
	return $date;
}