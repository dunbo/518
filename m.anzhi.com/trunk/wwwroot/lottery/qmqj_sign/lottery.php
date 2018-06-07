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
	exit(json_encode(array('code'=>0,'msg'=>'签到领取失败！')));
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


//检查此礼券是否领取过
$kind_award_list = get_user_kind_award_list($uid,$active_id,'qmqj_sign','valentine_draw_award');
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

//检查当天是否签到
$tm_con = get_config($active_id, $uid);
if($tm_con[date('Y-m-d',$time)]['status'] == 1){
	exit(json_encode(array('code'=>0,'msg'=>'您签到过了')));
}
//检查当前时间与position值是否对应
if($tm_con[date('Y-m-d',$time)]['num'] != $position){
	exit(json_encode(array('code'=>0,'msg'=>'参数有误!')));
}

//获取软件信息
$soft_info = getSoftInfoByAid($active_id);

try {
	load_helper('task');
	$task_client = get_task_client();
	$new_array=array(
			'uid'		=>	$uid,
			'aid'		=>	$active_id,
			'username'	=>	$_SESSION['USER_NAME'],
			'prefix'	=>	$prefix,
			'position'	=>	$position,
			'appName'	=>	!empty($soft_info) ? $soft_info['softname'] : '',
			'activityName' => '全民奇迹签到抽奖活动',
	);
	$the_award = $task_client->do('dota_sign', json_encode($new_array));
} catch (Exception $e) {
	$redis -> delete($aollow_key);
	exit(json_encode(array('code'=>0, 'msg'=>'服务器异常！')));
}

$lottery_rs = json_decode($the_award,true);
if($lottery_rs['code'] == 1){	
	//领取成功日志
	$log_data = array(
		"imsi"			=>	$_SESSION['USER_IMSI'],
		"device_id"		=>	$_SESSION['DEVICEID'],
		"activity_id"	=>	$active_id,
		"ip"			=>	$_SERVER['REMOTE_ADDR'],
		"sid"			=>	$sid,
		"time"			=>	$time,
		"award_level"	=>	$_POST['position'],
		"user"			=>	$_SESSION['USER_NAME'],
		'uid'			=>	$uid,
		"award_name"	=>	$lottery_rs['prizename'],
		'key'			=>	'award',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	
	//领取成功缓存数据
	$award_info_key = "{$prefix}:{$active_id}_user_draw_award_info:{$uid}:{$position}";
	$redis -> set($award_info_key, $lottery_rs, 15*86400);
	//设置已签到状态
	$tm_con[date('Y-m-d',$time)]['status'] = 1;
	$redis -> set("{$prefix}:{$active_id}_tm_config:".$uid, $tm_con, 10*86400);
	//增加签到数
	$sign_num_key = "{$prefix}:{$active_id}:user_sign_num:".$uid;
	$redis -> setx('incr', $sign_num_key, +1);
	$redis -> delete("{$prefix}:{$active_id}_user_draw_award:{$uid}");
}else{
	//签到领取失败 删除锁
	$redis -> delete($aollow_key);
}
exit($the_award);