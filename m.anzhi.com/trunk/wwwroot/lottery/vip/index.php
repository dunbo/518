<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$time = time();
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($active_id)){
	echo '活动id无效';
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$stop = $_GET['stop'] ? $_GET['stop'] : $_POST['stop'];
//@type 1会员招募 2会员招募8折
$type = $_GET['type'] ? $_GET['type'] : $_POST['type'];
$type = $type ? $type : 1; 
session_begin($sid);	
$prefix = 'vip';
if($stop != 1){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $activity_host."/lottery/{$prefix}/index.php?stop=1&type=".$type."&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['type'] = $type;
if($_GET['click']){
	$log_data = array(
		"activity_id" => $active_id,
		"chl_cid" => $_SESSION['CHL_CID'] ? $_SESSION['CHL_CID'] : '',
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		'product' => $_SESSION['product'],//1市场 13 sdk
		"click" => $_GET['click'],//1开通会员2会员权益页
		'key' => 'go_jump',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	if($_GET['click'] == 2){
		$tplObj -> out['new_static_url'] = $configs['new_static_url'];
		if($type == 1){
			$tpl = "lottery/vip/detail.html";	
		}else{
			$tpl = "lottery/vip/detail_".$type.".html";	
		}
		$tplObj -> display($tpl);
	}
	exit;
}
//日志
$log_data = array(
	"imsi"	=>	$_SESSION['USER_IMSI'],
	"device_id"		=>	$_SESSION['DEVICEID'],
	"chl_cid" => $_SESSION['CHL_CID'] ? $_SESSION['CHL_CID'] : '',
	"mac" 	=>  $_SESSION['MAC'],
	"ip"	=>	$_SERVER['REMOTE_ADDR'],
	"sid"	=>	$sid,
	"time"	=>	$time,
	'product' => $_SESSION['product'],//1市场 13 sdk
	"user"	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
	'uid'	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
	'key'	=>	'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

if(is_weixin() || $_GET['is_weixin']){
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tpl = "lottery/vip/weixin.html";	
	$tplObj -> display($tpl);	
	exit;
}

$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['is_sdk'] = $_SESSION['product'] == 13 ? 1 : 0;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['activity_host'] = $configs['activity_url'];
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['soft_info']  =  $soft_info;
$tplObj -> out['is_test'] = $configs['is_test'];
if($type == 1){
	$tpl = "lottery/vip/index.html";	
}else{
	if($stop == 1){
		$tpl = "lottery/vip/end_".$type.".html";	
	}else{
		$tpl = "lottery/vip/index_".$type.".html";	
	}
}
$tplObj -> display($tpl);
