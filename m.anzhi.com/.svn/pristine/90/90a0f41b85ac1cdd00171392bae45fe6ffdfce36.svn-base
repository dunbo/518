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
			'key'		=>	'info_edit'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
		exit(json_encode(array('code'=>1,'msg'=>'提交成功')));
	}else {
		exit(json_encode(array('code'=>0,'msg'=>'失败')));
	}
}else {
	if($_GET['types'] == 1) {
		//我的奖品
		list($giftlist,$award_list) = get_user_award($uid);
		//var_dump($giftlist,$award_list);
		$tplObj -> out['gift_award_arr'] = $giftlist;
		$tplObj -> out['kind_award_arr'] = $award_list;
		$tplObj -> out['is_coupon'] = $is_coupon;
		$tplObj -> out['is_practicality'] = $is_practicality;
	}else if($_GET['types'] == 2) {
		
		$tplObj -> out['now'] = date('Y-m-d',$time);
		$tplObj -> out['prizename']	=	$_GET['prizename'];
		$tplObj -> out['gift_num']	=	$_GET['gift_num'];
	}
	if($_GET['stop'] == 1) {
		$tplObj -> out['stop'] = 1;
	}		
	
	//用户信息
	$userinfo = get_user_info_new($uid,$active_id, $prefix, "valentine_draw_userinfo");
	//是否是sdk
	if($_SESSION['product']==1) {
		$is_sdk = 0;
	}else {
		$is_sdk = 1;
	}
	
	$tplObj -> out['is_sdk'] = $is_sdk;
	$tplObj -> out['package'] = 'com.wanmei.zhuxian.anzhi';
	$tplObj -> out['types'] = isset($_GET['types']) ? $_GET['types'] : 0;
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];	
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['sid'] = $sid;
	$tplObj -> out['prefix'] = $prefix;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
	$tplObj -> display("lottery/{$prefix}/userinfo.html");	
}