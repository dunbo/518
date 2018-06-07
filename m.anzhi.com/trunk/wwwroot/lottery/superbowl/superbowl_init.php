<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
include_once (dirname(realpath(__FILE__)).'/superbowl_fun.php');
$model = new GoModel();
$active_info  = getActiveConfig();
$activity_config = json_decode($active_info['configcontent'],true);

$active_id = $activity_config['activity_id'];
$vote_app_num = "super_bowl_active_{$active_id}_vote_app:";//软件投票数
//var_dump($activity_config);
// 活动日志
$activity_log_file = "activity_{$active_id}.log";
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}


// cdn资源地址
$tplObj -> out['static_url'] = $configs['static_url'];
// 加载session，获得用户相关信息
session_begin();

$sid = $_GET['sid'];
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}

$imsi_status = 0;

//无sim卡
if(!$imsi || $imsi == '000000000000000'){
	$imsi = '';
} else {
    $imsi_status = 1;
}
if($imsi_status == 0){
	//无sim卡跳转至无sim页面
	$tplObj->display('superbowl/superbowl_no_sim.html');
	exit();
}
$rkey_imsi_lottery_num = "superbowl_{$active_id}_{$imsi}_lottery_num";//用户可抽奖次数
$vote_app_num = "super_bowl_active_{$active_id}_vote_app:";//软件投票数
$tplObj->out['aid'] = $active_id;
$tplObj->out['sid'] = $sid;
$tplObj->out['imsi_status'] = $imsi_status;

// 判断版本是否大于等于5700
$cid = $_SESSION['MODEL_CID'];
$alone_update = $_SESSION['alone_update'];
//var_dump($_SESSION);
$market_info = chk_market_version($alone_update,$cid);

if($market_info[0]!=1){
	// 记日志
	$log_data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
		 	'time' => time(),
			'users' => '',
			'uid' => '',
			'key' => 'update_page'
	);
	//var_dump($log_data);

	permanentlog($activity_log_file, json_encode($log_data));
	// 跳转到提示升级页面
	$tplObj->out['check_status'] = $market_info[0];
	$tplObj->out['intro_result'] = $market_info[1];
	$tplObj->display("superbowl/superbowl_update.html");
	exit;
}


