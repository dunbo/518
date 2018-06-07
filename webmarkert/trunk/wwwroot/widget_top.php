<?php
include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');

$id = isset($_GET['id']) ? $_GET['id'] : 1;
// 日/周/月 排行
$params = array('GO_METHOD' => 'hotnd','ID' => $id,'LIST_INDEX_START' => 0,'LIST_INDEX_SIZE' => 11, 'VR' => 1);
if($channel == '360_app'){$params['PARENT_CAT_ID'] = 1;}else if($channel == '360_game'){$params['PARENT_CAT_ID'] = 2;}
$day_soft_arr = gomarket_action('soft.GoGetSoftList',$params);
$day_soft = $day_soft_arr['DATA'];
foreach($day_soft as $key => $val){
	$val['download'] = num_format($val[9],2);
	$day_soft[$key] = $val;
}

$tplObj -> out['day_soft'] = $day_soft;
$tplObj -> out['top'] = $id;
$tplObj -> out['channel'] = $_GET['channel'];

display('widget_top.html');

