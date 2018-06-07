<?php
include_once ('./fun.php');
session_begin();
$build_query = http_build_query($_GET);
$url = $activity_host."/lottery/{$prefix}/index.php?".$build_query;

if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{
	//未登录 跳转到首页
	if($_POST) {
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else {
		header("Location: {$url}");
	}
}

$g_num		=	isset($_POST['g_num']) && $_POST['g_num'] ? (Int)$_POST['g_num'] : null;
$position	=	isset($_POST['position']) && $_POST['position'] ? (Int)$_POST['position'] : null;

if( !$position ) {
	exit(json_encode(array('code'=>0,'msg'=>'翻卡失败！')));
}
//领取日志
$log_data = array(
	"imsi"			=>	$_SESSION['USER_IMSI'],
	"device_id"		=>	$_SESSION['DEVICEID'],
	"activity_id"	=>	$active_id,
	"ip"			=>	$_SERVER['REMOTE_ADDR'],
	"sid"			=>	$sid,
	"time"			=>	$time,
	"award_level"	=>	$position,
	"user"			=>	$_SESSION['USER_NAME'],
	'uid'			=>	$uid,
	'key'			=>	'lottery',
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	

if( stripos($_SERVER['HTTP_HOST'],'anzhi.com') == false ) {
	exit(json_encode(array('code'=>0,'msg'=>'请求异常！')));
}

//加锁
$aollow_key	=	$prefix.':'.$active_id.':'.$uid.':'.$g_num;
$res		=	$redis -> setnx($aollow_key, 1, 60);
if( $res === false ) {
	exit(json_encode(array('code'=>0,'msg'=>'操作频繁')));
}

$user_num = get_user_lottery_num($uid);
if( ($user_num['lottery_num']+$user_num['def_num']) <= $user_num['end_num'] ) {
	exit(json_encode(array('code'=>0,'msg'=>'您没有翻牌的机会')));
}

$award = lottery_do($active_id, $uid, $position, $_SESSION);
if($award) {
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
			"award_name"	=>	'',
			'key'			=>	'award',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	//删除锁
	$redis -> delete($aollow_key);
	if( $award == -1 ) {
		$param = array(
				'type'			=>	0,
				'activityId'	=>	$active_id,
				'LRTS'			=>	1,
				'status'		=>	2,
				'activityName'	=>	$activity['name'],
				'activityType'	=>	7,
		);
		$param_json = json_encode($param);
		exit( json_encode(array('code'=>-1, 'data'=> $param_json, 'msg'=>'未中奖')) );
	}else {
		$data_arr = json_decode($award, true);
		$app_info = array();
		if( $data_arr['package'] ) {
			$app_info = get_app_info($data_arr['package']);
		}
		$param = array(
			'activityName'	=>	$activity['name'],
			'activityType'	=>	7,
			'type'			=>	0,
			'activityId'	=>	$active_id,
			'redPackId'		=>	$data_arr['red_package_id'],
			'LRTS'			=>	1,
			'APP_INFO'		=>	$app_info?json_decode($app_info, true):"",
			'status'		=>	$data_arr['package']?($app_info?0:3):0,
			'orderId'		=>	$data_arr['insertid'],
		);
		$param_json = json_encode($param);
		exit( json_encode(array('code'=>1, 'data'=> $param_json, 'msg'=>'成功')) );
	}
}else {
	//领取失败 删除锁
	$redis -> delete($aollow_key);
	exit( json_encode(array('code'=>0, 'msg'=>'抽奖失败')) );
}
