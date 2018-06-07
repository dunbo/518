<?php
include_once ('./fun_2017.php');
session_begin();
$build_query = http_build_query($_GET);
$url = $activity_host."/lottery/{$prefix}/index.php?".$build_query;

if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}

if($_POST) {
	$data = array(
		'uid' => $uid,
		'aid' => $active_id,
		'username' => $_SESSION['USER_NAME'],
		'phone' => $_POST['mobile_phone'],
		'contact_name' => $_POST['lxname'],
		'address' => $_POST['address'],
	);
	$ret = add_user_new($data,$time,"{$prefix}","valentine_draw_userinfo");	
	if($ret) {
		//用户修改信息日志
		$log_data = array(
				'uid'			=>	$uid,
				'username'		=>	$_SESSION['USER_NAME'],
				'phone'			=>	$_POST['mobile_phone'],
				'contact_name'	=>	$_POST['lxname'],
				'address'		=>	$_POST['address'],
				'time'			=>	$time,
				'activity_id'	=>	$active_id,
				'key'			=>	'info_edit'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		exit(json_encode(array('code'=>1,'msg'=>'提交成功')));
	}else {
		exit(json_encode(array('code'=>0,'msg'=>'失败')));
	}
}else {	
	if($_GET['types']) {
		//用户信息
		$userinfo	=	get_user_info_new($uid,$active_id, $prefix, "valentine_draw_userinfo");
		$tplObj -> out['phone']			=	$userinfo['phone'];
		$tplObj -> out['contact_name']	=	$userinfo['contact_name'];
		$tplObj -> out['address']		=	$userinfo['address'];
	}else {
		//我的奖品
		//field  包名 类型 礼包码 礼包名称 必须有
		$kind_award_list = get_user_kind_award_list($uid, $active_id);
		$moneyInfo	=	get_azmoney($uid);
		$tplObj -> out['azmoney']		=	$moneyInfo['azmoney'];
		$tplObj -> out['kind_award_list'] = $kind_award_list;
	}
	
	if($_GET['stop'] == 1 && $_GET['types'] == 3) {
		$tpl = "lottery/{$prefix}/userinfo_end_2017.html";	
	}else{
		$tpl = "lottery/{$prefix}/userinfo_2017.html";	
	}
	
	//是否是sdk
	if($_SESSION['product']==1) {
		$is_sdk = 1;
	}else {
		$is_sdk = 0;
	}
	
	$tplObj -> out['is_test']	=	$configs['is_test'];
	$tplObj -> out['is_sdk']	=	$is_sdk;
	$tplObj -> out['types']		=	!empty($_GET['types']) ? 1 : 0;
	$tplObj -> out['aid']		=	$active_id;
	$tplObj -> out['sid']		=	$sid;
	$tplObj -> out['prefix']	=	$prefix;
	$tplObj -> out['stop'] = $_GET['stop'];	
	$tplObj -> out['static_url']		=	$configs['static_url'];
	$tplObj -> out['new_static_url']	=	$configs['new_static_url'];
	$tplObj -> out['version_code']		=	$_SESSION['VERSION_CODE'];
	$tplObj -> display($tpl);
}