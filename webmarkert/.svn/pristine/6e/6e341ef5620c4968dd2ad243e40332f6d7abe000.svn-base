<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
define('STATDB', 'gostats');
$max_days = 15;
//$dev_id = isset($_GET['dev_id']) ? $_GET['dev_id'] : '';
$softid = isset($_GET['softid']) ? $_GET['softid'] : '';
$soft_info = gomarket_action('soft.GoGetSoftDetailCategory', array('ID' => $softid,'VR' => 1,'EXTRA_OPTION_FIELD' => array('A.dev_id'))) ;

if ((!empty($_SESSION['user_data']['userid']) || !empty($_SESSION['user_data']['user_name']))&&($soft_info['dev_id'] == $_SESSION['user_data']['userid'] || in_array($_SESSION['user_data']['user_name'], $GLOBALS["CP_ADMINS"]))) {
   $return = true;
}else{
   $return = false;
}
if (!$return) exit;

$now = time();
$days = array();
$categories = array();
for ($i = $max_days ;$i > 0;$i--) {
	$n = $max_days - 1 - $i;
    $time = $days[] = strtotime(date("Y-m-d 00:00:00", $now - $i * 24 * 60 * 60));
	$categories[] = date("n/d", $time);
}

$softObj = new GoModel();
$option = array(
	'table' => 'sj_download_count',
	'where' => array(
		'package' => $soft_info['PACKAGENAME'],
		'submit_day' => $days
	),
);
$history = $softObj->findAll($option, STATDB);
$jsdata = array();

$option_detain = array(
  'table' => 'sj_download_detain',
  'where' => array(
    'package' => $soft_info['PACKAGENAME'],
    //'package' => "aimoxiu.theme.yhxfodgg",
    'submit_day' => $days
  ),
);
$detain_client = $softObj->findAll($option_detain, STATDB);

$option_add = array(
  'table' => 'sj_download_add',
  'where' => array(
    'package' => $soft_info['PACKAGENAME'],
    'submit_day' => $days
  ),
  'group' => 'submit_day',
  'field' => 'sum(add_cnt) add_cnt, submit_day'
);
$add_client = $softObj->findAll($option_add, STATDB);

$detain = array();
$add = array();
$sum_client = array();
$sum_web = array();
$sum_total = array();
foreach ($detain_client as $v) {
    $day = date('n/d',$v['submit_day']);
    $detain[$day] = $v['detain_cnt'];
}
foreach ($add_client as $v) {
    $day = date('n/d',$v['submit_day']);
    $add[$day] = $v['add_cnt'];
}
$package_n = 0;
foreach ($history as $idx => $val) {
	//异常，以09/07为基准
	if ($val['submit_day'] == 1441555200) {
		$package_n = round($val['mob_up_cnt']/$val['mob_dl_cnt'], 2);
	}
	
	if ($val['submit_day'] > 1441555200 && $val['mob_up_cnt'] >488000 && !empty($package_n)) {
		$val['mob_up_cnt'] = floor($package_n * $val['mob_dl_cnt']);
	}
	
	$day = date('n/d',$val['submit_day']);
	
	if (isset($sum_web[$day])){
		$sum_web[$day]+= $val['web_dl_cnt'];
	}else{
		$sum_web[$day] = $val['web_dl_cnt'];
	}

  if (isset($sum_total[$day])){
    $sum_total[$day]+= $val['web_dl_cnt'] + $val['mob_dl_cnt'] + $val['mob_up_cnt'] + $val['ptn_dl_cnt'] + $val['wap_dl_cnt'];
  }else{
    $sum_total[$day] = $val['web_dl_cnt'] + $val['mob_dl_cnt'] + $val['mob_up_cnt'] + $val['ptn_dl_cnt'] + $val['wap_dl_cnt'];
  }
	
	if (isset($sum_client[$day])){
		$sum_client[$day]+= ($val['mob_dl_cnt'] + $val['mob_up_cnt']);
	}else{
		$sum_client[$day] = ($val['mob_dl_cnt'] + $val['mob_up_cnt']);
	}
	if($add[$day]){
	    $sum_client[$day] = $sum_client[$day] + $add[$day];

      $sum_total[$day] = $sum_total[$day] + $add[$day];
      
	    unset($add[$day]);
	}

  if($detain[$day]){
      $sum_client[$day] = $sum_client[$day] - $detain[$day];
      $sum_client[$day]<0 && $sum_client[$day] = 0;

      $sum_total[$day] = $sum_total[$day] - $detain[$day];
      $sum_total[$day]<0 && $sum_total[$day] = 0;
      
      unset($detain[$day]);
  }
	
}
$web_data = array();
$client_data = array();
$total_data = array();

$d_config = array(
    'com.tencent.qqmusic' => 0.20,//qq音乐
    'com.sds.android.ttpod' => 0.25,//天天动听
    'cn.kuwo.player' => 0.45,//酷我
    'com.kugou.android' => 0.35,//酷狗
    'com.duomi.android' => 0.50,//多米
);
$start_date = strtotime('2013-01-12');
$end_date = strtotime('2015-01-08');
foreach ($categories as $day) {
    $web_cnt = intval($sum_web[$day]);
    $client_cnt = intval($sum_client[$day]);
    $total_cnt = intval($sum_total[$day]);
    $d = strtotime('2015 '. $day);
    
    if (isset($d_config[$soft_info['PACKAGENAME']]) && $d <= $end_date) {
        $web_cnt = floor($web_cnt * (1-$d_config[$soft_info['PACKAGENAME']]));
        $client_cnt = floor($client_cnt * (1-$d_config[$soft_info['PACKAGENAME']]));
        $total_cnt = floor($total_cnt * (1-$d_config[$soft_info['PACKAGENAME']]));
    }
    $web_data[] = $web_cnt;
    $client_data[] = $client_cnt;
    $total_data[] = $total_cnt;
}

$series = array(
  /*
	array(
		'name' => 'web下载量',
		'data' => $web_data,
	),
	array(
		'name' => '客户端下载量',
		'data' => $client_data,
	),
  */
  array(
    'name' => '总下载量',
    'data' => $total_data,
  )
);

$categories = json_encode($categories);
$series = json_encode($series);
$callback = <<<EOF
var chart;
$.getScript("/js/Highcharts-3.0.1/js/highcharts.js",function(){
$('#photo').show();
chart = new Highcharts.Chart({
      chart: {
         renderTo: 'photo',
         defaultSeriesType: 'line',
         marginRight: 30
      },
	  credits: false,
      title: {
         text: '最近 {$max_days} 天下载量',
         x: -20 //center
      },

      xAxis: {
         categories: {$categories}
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
         align: 'right',
         verticalAlign: 'top',
         x: 0,
         y: 0,
         borderWidth: 0
      },
      series: {$series}
   });
}
);

EOF;
if ($return) exit($callback);
