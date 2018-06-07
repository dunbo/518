<?php
include_once ('./fun.php');
session_begin();
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = $activity_host."/lottery/{$prefix}/index.php";
	exit(json_encode(array('code'=>2,'url'=>$url)));
}
$position = isset($_POST['position']) && $_POST['position'] ? (Int)$_POST['position'] : null;
if( !$position ) {
	exit(json_encode(array('code'=>0,'msg'=>'领取失败！')));
}
//领取日志
$log_data = array(
	"imsi" => $_SESSION['USER_IMSI'],
	"device_id" => $_SESSION['DEVICEID'],
	"activity_id" => $active_id,
	"ip" => $_SERVER['REMOTE_ADDR'],
	"sid" => $sid,
	"time" => $time,
	"award_level" => $position,
	"user" => $_SESSION['USER_NAME'],
	'uid'=>$uid,
	'key' => 'lottery',
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
//获取我的签到次数
$sign_num = get_sign_num($uid);
switch ( $position ) {
	case 1:
		$sign_rule_num = 3;
		break;
	case 2:
		$sign_rule_num = 7;
		break;
	case 3:
		$sign_rule_num = 15;
		break;
	case 4:
		$sign_rule_num = 30;
		break;
	case 5:
		$sign_rule_num = 30;
		break;
	default:
		exit(json_encode(array('code'=>0,'msg'=>'领取失败！')));
		break;
}

if( $sign_num < $sign_rule_num ) {
	exit(json_encode(array('code'=>0,'msg'=>'签到次数不够！')));
}

$award_expire = get_award_expire($uid, array($sign_rule_num));
if(isset($award_expire[$sign_rule_num]) && $award_expire[$sign_rule_num] == 1) {
	exit(json_encode(array('code'=>0,'msg'=>'礼券已失效！')));
}

//检查此礼券是否领取过
$kind_award_list = get_user_kind_award_list($uid,$active_id,'september_sign','valentine_draw_award');
if( isset($kind_award_list[$position]) ) {
	exit(json_encode(array('code'=>0,'msg'=>'已经领取过了！')));
}

if(stripos($_SERVER['HTTP_HOST'],'anzhi.com') == false) {
	exit(json_encode(array('code'=>0,'msg'=>'请求异常！')));
}

//防刷处理
$aollow_key = $prefix.':'.$active_id.':'.$uid.':'.$position;
$res	    = $redis -> setnx($aollow_key, 1, 86400);
if( $res === false ) {
	exit(json_encode(array('code'=>0,'msg'=>'已经处理过了！')));
}

load_helper('task');
$task_client = get_task_client();
$new_array=array(
	'uid'=>$uid,
	'aid'=>$active_id,
	'username'=>$_SESSION['USER_NAME'],
	'prefix'=>$prefix,
	'position' => $position,
);		
$the_award = $task_client->do('smashed_egg', json_encode($new_array));	
$lottery_rs = json_decode($the_award,true);
if($lottery_rs['code'] == 1){
	if($lottery_rs['msg'] == '失败'){
		$redis -> delete($aollow_key);
		exit($the_award);
	}
	$arr = array(	
		'uid' => $uid,
		'pid' =>  $lottery_rs['pid'],
		'prizename' => $lottery_rs['prizename'],
		'time' => date("Y-m-d",$time)
	);		
	//save_luk($uid,$position);
	//领取成功日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"award_level" => $_POST['position'],
		"user" => $_SESSION['USER_NAME'],
		'uid'=>$uid,
		"award_name" =>  $lottery_rs['prizename'],
		'key' => 'award',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$redis -> delete("{$prefix}:{$active_id}_user_draw_award:{$uid}");
	$redis -> delete($aollow_key);
}else{
	$redis -> delete($aollow_key);
}
exit($the_award);
