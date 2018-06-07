<?php
include_once ('./fun.php');
session_begin();
if($_GET['stop'] == 1){
	list($award_list,$integral_kind_record_all,$award_count,$integral_kind_count) = get_award_all($active_id);
	$tplObj -> out['award_list'] = $award_list;
	$tplObj -> out['integral_kind_record_all'] = $integral_kind_record_all;
	$tplObj -> out['award_count'] = $award_count;
	$tplObj -> out['integral_kind_count'] = $integral_kind_count;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> display('lottery/christmas/end.html');	
	exit;
}
$time = time();
$rand_num = rand(1,2);
//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176  ? $_SESSION['USER_NAME'] : '',
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'type'=> $rand_num,//1是抽奖页面2是积分兑换
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
$build_query = http_build_query($_GET);
if($rand_num == 1){
	$url = "http://promotion.anzhi.com/lottery/christmas/lottery.php?".$build_query;	
}else{
	$url = "http://promotion.anzhi.com/lottery/christmas/integral_award.php?".$build_query;	
}
header("Location: {$url}");
exit;
