<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/my_name_MT3/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
session_begin();
$time = time();
$sid = $sid ? $sid : session_id();
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
//user_loging_new();
user_loging_reserve();
$cook_is_jump = $_COOKIE['IS_JUMP'];//端外跳转
if((isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176)||$cook_is_jump==1)//已登录
{
	if($_GET['lgfrom']==1||$_SESSION['REVE_TYPE']=='login') //通过页面登录来的
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
	}
	elseif($_GET['lgfrom']==2||$_SESSION['REVE_TYPE']=='register')//通过页面注册来的
	{
		//注册日志
		$log_data = array(
			'uid' => $_SESSION['USER_UID'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $sid,
			'time' => $time,
			'key' => 'register'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	}
	else
	{
		//通过安智市场来的
		$log_data = array(
			'uid' => $_SESSION['USER_UID'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $sid,
			'time' => $time,
			'key' => 'anzhi_market'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	}
	$uid = $_SESSION['USER_UID'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;

	//todo 是否预约
	$res = $redis->get("my_name_MT3:{$active_id}_is_sign:".$uid);
	if($res == 1||$cook_is_jump==1)
	{
		$tplObj -> out['is_sing'] = 1;
	}
	else
	{
		if($_GET['lgfrom']||$_SESSION['REVE_TYPE']) //不管是登录还是注册都是预约成功的
		{
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


			$redis->set("my_name_MT3:{$active_id}_is_sign:".$uid,1,86400*30);
			setcookie('IS_JUMP', '1', time()+24 * 3600 * 15, '/', 'anzhi.com');
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
$tpl = "lottery/my_name_MT3/index.html";
if($_SERVER['SERVER_ADDR'] == '118.26.203.23')
{
	$share_url = 'http://118.26.203.23/a_'.$active_id.'.html';
}
elseif($_SERVER['SERVER_ADDR'] == '192.168.0.99')
{
	$share_url = 'http://9.m.anzhi.com/a_'.$active_id.'.html';
}
else 
{
	$share_url = 'http://fx.anzhi.com/a_'.$active_id.'.html';
}

$tplObj -> out['imsi'] = $_SESSION['USER_IMSI'];
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['share_text'] = "经典再续《我叫MT3》即刻预约领豪华礼包！";
$tplObj -> out['share'] = $_GET['share'];
$tplObj -> out['share_url'] = $share_url;
$tplObj -> out['is_stop'] = $_GET['stop'];
$tplObj -> display($tpl);
