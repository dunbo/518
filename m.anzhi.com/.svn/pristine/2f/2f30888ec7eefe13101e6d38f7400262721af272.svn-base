<?php
include_once ('./fun.php');
session_begin($sid);
$url = "{$activity_host}/lottery/{$prefix}/index.php?aid={$active_id}&sid={$sid}";
if(isset($_SESSION['USER_UID'])) {
	//已登录
	$uid = $_SESSION['USER_UID'];
}else{
	//未登录 跳转到首页
	if($_POST) {
		exit(json_encode(array('code'=>2, 'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}

$activity = get_config($active_id);
$data = array(
		'type'		=>	3,
		'sTime'		=>	date('Y-m-d H:i:s', $activity['start_tm']),
		'eTime'		=>	date('Y-m-d H:i:s', $activity['end_tm']),
		'yesUid'	=>	array($uid),
	);
//消费安智币
// $xfmoney = get_xf_info($data);
// if($xfmoney < 100000) {
// 	header("Location: {$url}");
// }

if($_POST) {
	$data = array(
		'uid'	=>	$uid,
		'aid'	=>	$active_id,
		'username'	=>	$_SESSION['USER_NAME'],
		'phone'		=>	$_POST['mobile_phone'],
		'contact_name'	=>	$_POST['lxname'],
		'address'		=>	$_POST['address'],
	);
	$ret = add_user($data, $time);
	if($ret) {
		//用户修改信息日志
		$log_data = array(
			'uid'		=>	$uid,
			'username'	=>	$_SESSION['USER_NAME'],
			'phone'		=>	$_POST['mobile_phone'],
			'contact_name'	=>	$_POST['lxname'],
			'address'	=>	$_POST['address'],
			'time'		=>	$time,
			'activity_id'	=>	$active_id,
			'key'		=>	'user_info'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
		exit(json_encode(array('code'=>1,'msg'=>'提交成功')));
	}else {
		exit(json_encode(array('code'=>0,'msg'=>'失败')));
	}
}

//用户信息
$userinfo = get_user_info_new($uid,$active_id, $prefix, "valentine_draw_userinfo");

$tplObj -> out['phone'] = $userinfo['phone'];	
$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
$tplObj -> out['address'] = $userinfo['address'];	
$tplObj -> out['aid'] = $active_id;	
$tplObj -> out['sid'] = $sid;
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
$tplObj -> display("lottery/{$prefix}/userinfo.html");	
