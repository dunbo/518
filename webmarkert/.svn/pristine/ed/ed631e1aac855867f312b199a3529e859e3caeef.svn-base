<?php
include_once (dirname ( realpath ( __FILE__ ) ) . '/init.php');

$id = isset($_GET['id']) ? $_GET['id'] : 1;
// 日/周/月 排行
/*$day_soft_arr = gomarket_action('soft.GoGetSoftList',array('GO_METHOD' => 'hotnd','ID' => $id,'LIST_INDEX_START' => 0,'LIST_INDEX_SIZE' => 11));
$day_soft = $day_soft_arr['DATA'];
foreach($day_soft as $key => $val){
	$val['download'] = num_format($val[9],2);
	$day_soft[$key] = $val;
}
$tplObj -> out['day_soft'] = $day_soft;
*/
if($_COOKIE['_AZ_EMAILSTATUS_'] == 2){
	$tplObj -> out['point'] = 1;
}

$tplObj->display('widget_user.html');

