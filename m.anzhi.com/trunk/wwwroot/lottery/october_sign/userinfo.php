<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
//$active_id =312;
$sid = $_POST['sid'];
$active_id =  $_POST['aid'];
session_begin();
$url = "http://promotion.anzhi.com/lottery/october_sign/index.php?aid={$active_id}&sid={$sid}";
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}

if($_POST){
	//用户修改信息日志
	$log_data = array(
		'uid'=>$uid,
		'username' => $_SESSION['USER_NAME'],
		'phone' => $_POST['mobile_phone'],
		'contact_name' => $_POST['lxname'],
		'address' => $_POST['address'],
		'time' => time(),
		'activity_id' => $active_id,
		'key' => 'user_info'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));		
    $redis->set('october_userinfo'.$uid,$log_data,86400*10);
	exit(json_encode(array('code'=>1,'msg'=>'成功')));
}
