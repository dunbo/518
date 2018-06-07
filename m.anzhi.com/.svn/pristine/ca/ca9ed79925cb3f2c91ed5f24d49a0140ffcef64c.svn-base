<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/peak_warship/index.php?lgfrom=1&".$build_query;
$tplObj -> out['login_url'] = $login_url;
session_begin();
$time = time();
//$time = get_now_time();
//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
user_loging_new();
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176)//已登录
{
	//登录日志
	$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $active_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $sid,
		'time' => $time,
		'key' => 'login'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$uid = $_SESSION['USER_UID'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;

	//todo 是否预约
	$res = $redis->get("peak_warship:{$active_id}_is_sign:".$uid);
	if($res == 1)
	{
		$tplObj -> out['is_sing'] = 1;
	}
	else
	{
		if($_GET['lgfrom']==1)
		{
			//$time = get_now_time();
			//用户预约日志
			$log_data = array(
					'uid'=>$uid,
					'username' => $_SESSION['USER_NAME'],
					'imsi' => $_SESSION['USER_IMSI'],
					'device_id' => $_SESSION['DEVICEID'],
					'time' => $time,
					'activity_id' => $active_id,
					'key' => 'sign'
			);
			permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	


			$redis->set("peak_warship:{$active_id}_is_sign:".$uid,1,86400*30);
			$tplObj -> out['is_sing'] = 1;
		}
		else
		{
		    $tplObj -> out['is_sing'] = 2;
        }
	}
}
else
{//未登录
    $tplObj -> out['is_sing'] = 2;
	$tplObj -> out['is_login'] = 2;
}
$tpl = "lottery/peak_warship/index.html";

$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['share_text'] = "《巅峰战舰》首款次世代TPS海战手游！等你来战！";
$tplObj -> out['share'] = $_GET['share'];
$tplObj -> out['is_stop'] = $_GET['stop'];
$tplObj -> display($tpl);
