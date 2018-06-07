<?php
include_once ('./201803_fun.php');

if($_SESSION['USER_UID']){
	//已登录
	$uid = $_SESSION['USER_UID'];
}else{
	//未登录 跳转到首页或返回状态码
	$url = $activity_host."/lottery/{$prefix}/{$tpl_prefix}_index.php";
	if($_POST){
		echo json_encode(array('code'=>2, 'url'=>$url,));
	}else{
		header("Location: {$url}?aid={$aid}");
	}
	exit;
}

if($_POST){
	$data = array(
		'uid' 			=> $uid,
		'aid' 			=> $active_id,
		'username' 		=> $_SESSION['USER_NAME'],
		'phone' 		=> $_POST['mobile_phone'],
		'contact_name' 	=> $_POST['lxname'],
		'address' 		=> $_POST['address'],
	);
	$ret = add_user_new($data, $time, "{$prefix}", "valentine_draw_userinfo");
	if($ret){
		//用户修改信息日志
		$log_data = array(
			'uid'			=>	$uid,
			'username'		=>	$_SESSION['USER_NAME'],
			'phone'			=>	$_POST['mobile_phone'],
			'contact_name'	=>	$_POST['lxname'],
			'address'		=>	$_POST['address'],
			'time'			=>	$time,
			'activity_id'	=>	$active_id,
			'key'			=>	'info_edit',
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		exit(json_encode(array('code'=>1, 'msg'=>'提交成功',)));
	}else {
		exit(json_encode(array('code'=>0, 'msg'=>'失败',)));
	}
}else{
	//用户信息
	$userinfo =	get_user_info_new($uid,$active_id, $prefix, "valentine_draw_userinfo");
	$tplObj -> out['phone']			=	$userinfo['phone'];
	$tplObj -> out['contact_name']	=	$userinfo['contact_name'];
	$tplObj -> out['address']		=	$userinfo['address'];
	$tplObj -> out['stop'] = $stop;
	$tpl = "lottery/{$prefix}/{$tpl_prefix}_userinfo.html";
	$tplObj -> display($tpl);
}