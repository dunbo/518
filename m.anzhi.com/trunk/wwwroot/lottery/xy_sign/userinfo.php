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
			'key'		=>	'user_info'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
		exit(json_encode(array('code'=>1,'msg'=>'提交成功')));
	}else {
		exit(json_encode(array('code'=>0,'msg'=>'失败')));
	}
}else {
	if($_GET['types'] == 1) {
		//我的奖品
		$kind_award_list = get_user_kind_award_new($uid,$active_id, $prefix, 'valentine_draw_award');
		$is_coupon = 0;
		$is_practicality = 0;
		if(!empty($kind_award_list)) {
			foreach ( (array)$kind_award_list as $k => $v) {
					$coupon = 0;
					if( strripos($v['prizename'], '礼券') ) {
						$is_coupon = 1;
						$coupon = 1;
					}else {
						$is_practicality = 1;
					}
					$kind_award_list[$k]['is_coupon'] = $coupon;
			}
		}
		$tplObj -> out['kind_award_arr'] = $kind_award_list;
		$tplObj -> out['is_coupon'] = $is_coupon;
		$tplObj -> out['is_practicality'] = $is_practicality;
	}else if($_GET['types'] == 2) {
		//恭喜中奖
		$prizename = $_GET['prizename'];
		//检查是否有礼券
		if(strripos($prizename, '礼券')) {
			$tplObj -> out['is_coupon'] = 1;
			$tplObj -> out['is_practicality'] = 0;
		}else{
			$tplObj -> out['is_coupon'] = 0;
			$tplObj -> out['is_practicality'] = 1;
		}
		$tplObj -> out['now'] = date('Y-m-d',$time);
		$tplObj -> out['prizename']	=	$prizename;
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
	$tplObj -> out['package'] = 'com.netease.my.anzhi';//梦幻西游包名
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