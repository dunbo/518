<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$model = new GoModel();
$sid = $_GET['sid'];
if($sid && eregi('[0-9a-zA-z]', $sid) && strlen($sid) == 32){
	session_id($sid);
}
session_start();
$imsi = $_SESSION['USER_IMSI'];
$aid = $_GET['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($aid)){
	exit;
}
if($aid){
	$option = array(
		'where' => array(
			'id' => $aid
		),
		'cache_time' => 3600,
		'table' => 'sj_activity'
	);

	$result = $model -> findOne($option);
	if($result['start_tm'] > time()){
		$pid = $result['activity_prepare_id'];
	}elseif($result['start_tm'] <= time() && $result['end_tm'] >= time()){
		$pid = $result['activity_page_id'];
	}elseif($result['end_tm'] < time()){
		$pid = $result['activity_end_id'];
	}

}
/*elseif($_GET['pid']){
	$pid = $_GET['pid'];
}*/

if(!empty($_GET['pid'])){
    $pid = $_GET['pid'];
}

if($result['activity_type'] == 4 || $pid){
	$page_option = array(
		'where' => array(
			'id' => $pid
		),
		'cache_time' => 3603,
		'table' => 'sj_pre_download'
	);
	$page_result = $model -> findOne($page_option);
}

$page_result['first_texts'] = htmlspecialchars_decode($page_result['first_text']);
$page_result['second_texts'] = htmlspecialchars_decode($page_result['second_text']);
$page_result['third_texts'] = htmlspecialchars_decode($page_result['third_text']);
$first_focus_pic_arr = explode(',',$page_result['first_focus_pic']);
$second_focus_pic_arr = explode(',',$page_result['second_focus_pic']);
$third_focus_pic_arr = explode(',',$page_result['third_focus_pic']);
$log_data = array(
	'imsi' => $imsi,
	'device_id' =>  $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'key' => 'show_homepage'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));

$tplObj -> out['aid'] = $aid;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['result'] = $result;
$tplObj -> out['page_result'] = $page_result;
$tplObj -> out['img_url'] = getImageHost();
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['first_focus_pic_arr'] = $first_focus_pic_arr;
$tplObj -> out['second_focus_pic_arr'] = $second_focus_pic_arr;
$tplObj -> out['third_focus_pic_arr'] = $third_focus_pic_arr;
if($page_result['module'] == 1){
	$tplObj -> display('pre_download_one.html');
}elseif($page_result['module'] == 2){
	$tplObj -> display('pre_download_two.html');
}elseif($page_result['module'] == 3){
	$tplObj -> display('pre_download_three.html');
}

