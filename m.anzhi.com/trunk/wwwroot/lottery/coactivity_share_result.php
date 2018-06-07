<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
$aid = $_POST['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
$sid = $_POST['sid'];
session_begin($sid);
$appType = $_POST['appType'];
$resultType = $_POST['resultType'];
if($_SESSION['USER_IMSI']){
	$imsi = $_SESSION['USER_IMSI'];
}
$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'sid' => $sid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'time' => time(),
	//分享应用类型（0：短信，1：新浪微博，2：QQ空间，3：微信好友，4：微信朋友圈，6：QQ好友）
	'apptype' => $appType,
	//分享结果（1：分享成功，2：分享取消，3：分享失败）
	'resulttype' => $resultType,
	"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
	'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',	
	'key' => 'share_result'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));

