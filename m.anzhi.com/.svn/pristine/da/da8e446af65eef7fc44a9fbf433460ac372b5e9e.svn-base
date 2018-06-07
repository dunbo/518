<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$prefix = "reserve";
if($_GET['game_name'])
{
	$game_prefix=$_GET['game_name'];//用于文件路径的
	$game_name = $_GET['game_name'];//用于日志
}
else
{
	$game_name="royal_war";
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
if($_SERVER['SERVER_ADDR'] == '118.26.203.23'||$_SERVER['SERVER_ADDR'] == '192.168.0.99'){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
//$lifeTime = 24 * 3600; //一天
$lifeTime =24 * 3600 * 15;
session_set_cookie_params($lifeTime);  
	
session_begin($sid);
$time = time();
$now_day = date('Ymd');

if($_GET['game_name'])
{
	$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=034&serviceVersion=5410&serviceType=2&redirecturi=";
}
else
{
	$center_url = "http://".$h_str."i.anzhi.com/mweb/account/reg?serviceId=034&serviceVersion=5410&serviceType=2&redirecturi=";
}
$login_url = $center_url."http://promotion.anzhi.com/lottery/{$prefix}/reserve.php?p=1&game_name={$game_prefix}";
$tplObj -> out['login_url'] = $login_url;		
user_loging_reserve();	
$sid = session_id();
//打开页面日志
$log_data_open = array(
	'uid' => $_SESSION['USER_UID'],
	'imsi' => $_SESSION['USER_IMSI'],
	'device_id' => $_SESSION['DEVICEID'],
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $sid,
	'time' => $time,
	'key' => 'open',
	'game_name'=>$game_name,
);
permanentlog('reserve.log', json_encode($log_data_open));
$p = $_GET['p'];
$cook_is_jump = $_COOKIE['IS_JUMP'];
if((isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176 && $p==1) || $cook_is_jump==1)
{
	$is_reserve=1;
	$tplObj -> out['is_reserve'] = 1;
} else {
	$is_reserve=2;
	$tplObj -> out['is_reserve'] = 2;
}
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176 && $is_reserve == 1)
{//说明已预约
	setcookie('IS_JUMP', '1', time()+24 * 3600 * 15, '/', 'anzhi.com');
	$uid = $_SESSION['USER_UID'];
	if($uid)
	{
		$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'ip' => $_SERVER['REMOTE_ADDR'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'sid' => $sid,
		'time' => $time,
		'key' => 'success',
		'game_name'=>$game_name,
		);
		permanentlog('reserve.log', json_encode($log_data));
	}
	$reserve_type=$_SESSION['REVE_TYPE'];
	if($reserve_type=="login")
	{
		$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'ip' => $_SERVER['REMOTE_ADDR'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'sid' => $sid,
		'time' => $time,
		'key' => 'login',
		'game_name'=>$game_name,
		);
		permanentlog('reserve.log', json_encode($log_data));
	}
	elseif($reserve_type=="register")
	{
		$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'ip' => $_SERVER['REMOTE_ADDR'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'sid' => $sid,
		'time' => $time,
		'key' => 'register',
		'game_name'=>$game_name,
		);
		permanentlog('reserve.log', json_encode($log_data));
	}else
	{
		$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'ip' => $_SERVER['REMOTE_ADDR'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'sid' => $sid,
		'time' => $time,
		'key' => 'third',
		'game_name'=>$game_name,
		);
		permanentlog('reserve.log', json_encode($log_data));
	}
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['uid'] = $uid;
}
//$last_time = strtotime("2016-4-15");
//$tplObj -> out['last_time'] = $last_time;
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['now'] = $time;	
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];	
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
$tplObj -> out['prefix'] = $prefix;	
if($game_prefix)
{
	$tplObj -> out['game_prefix'] = $game_prefix;	
	$tplObj -> display("lottery/{$prefix}/{$game_prefix}/index.html");	
}
else
{
	$tplObj -> display("lottery/{$prefix}/index.html");	
}
