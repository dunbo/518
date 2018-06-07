<?php
include_once ('./fun.php');
$day_limit_key = $prefix.":".$active_id.":limit_gm_num:".$imsi.":".$today;
if($_POST['num'] == 1){
	//玩游戏次数
	$num = $redis->setx('incr',$day_limit_key,+1);
	$redis->expire($day_limit_key,86400);
	if($num <= 3){
		$log_data = array(
				"imsi" => $imsi,
				"device_id" => $_SESSION['DEVICEID'],
				"activity_id" => $active_id,
				"ip" => $_SERVER['REMOTE_ADDR'],
				"sid" => $sid,
				"time" => $time,
				"user" => $_SESSION['USER_NAME'],
				'uid'=> $_SESSION['USER_UID'],
				'key' => 'game_num'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
		set_gm_num();
	}
	echo $num;
	exit;
}
$build_query = http_build_query($_GET);
if($configs['is_test'] == 1 ) {
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/xy2/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
//日志
$log_data = array(
		"imsi" => $imsi,
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $_SESSION['USER_UID'],
		'key' => 'page_index'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	

$tplObj -> out['aid'] = $active_id;
$tplObj -> out['gm_num'] = $redis->get($day_limit_key);
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['activity_host'] = $configs['activity_url'];
$tpl = "lottery/".$prefix."/page_index.html";	
$tplObj -> out['imsi'] = $imsi;
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['prefix'] = $prefix;
$tplObj -> display($tpl);