<?php
include_once ('./fun.php');
session_begin();
$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/{$prefix}/index.php?".$build_query;

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
	$data = array(
		'uid' => $uid,
		'aid' => $active_id,
		'username' => $_SESSION['USER_NAME'],
		'phone' => $_POST['mobile_phone'],
		'contact_name' => $_POST['lxname'],
		'address' => $_POST['address'],
	);
	$ret = add_user_new($data,$time,"{$prefix}","one_dollar_userinfo");
    if($ret){
		//用户修改信息日志
		$log_data = array(
			'uid'=>$uid,
			'username' => $_SESSION['USER_NAME'],
			'phone' => $_POST['mobile_phone'],
			'contact_name' => $_POST['lxname'],
			'address' => $_POST['address'],
			'time' => $time,
			'activity_id' => $active_id,
			'key' => 'info_edit'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
		exit(json_encode(array('code'=>1,'msg'=>'成功')));
	}else{
		exit(json_encode(array('code'=>0,'msg'=>'失败')));
	}
}else{
	if($_GET['types'] == 1 ||$_GET['types'] == 3){
		//抢夺记录
		$kind_award_list = get_user_kind_award($uid);
		$tplObj -> out['kind_award_arr'] = $kind_award_list;
		$kind_award_gift = get_user_kind_gift_new($uid,$active_id,"{$prefix}","valentine_draw_gift");
		$tplObj -> out['gift_prize_arr'] = $kind_award_gift;			
	}else if($_GET['types'] == 2){
		//兑换实物奖品
		$tplObj -> out['prizename'] = $_GET['prizename'];
		$tplObj -> out['integral'] = $_GET['num'];
		$tplObj -> out['position'] = $_GET['position'];
		$tplObj -> out['gift'] = $_GET['gift'];		
	}else if($_GET['types'] == 4){
		//我的奖品
		$kind_award_list = get_user_kind_award_new($uid,$active_id,"{$prefix}",'valentine_draw_award');
		$tplObj -> out['kind_award_arr'] = $kind_award_list;
		$kind_award_gift = get_user_kind_gift_new($uid,$active_id,"{$prefix}","valentine_draw_gift");
		$tplObj -> out['gift_prize_arr'] = $kind_award_gift;	
	}else if($_GET['types'] == 5){
		//中奖实物
		$tplObj -> out['now'] = date('Y-m-d');
		$tplObj -> out['prizename'] = $_GET['prizename'];
	}else if($_GET['types'] == 6){
		//中奖礼包
		$tplObj -> out['now'] = date('Y-m-d');
		$tplObj -> out['prizename'] = $_GET['prizename'];
		$tplObj -> out['package'] = $_GET['package'];
		$tplObj -> out['gift_num'] = $_GET['gift_num'];
		$tplObj -> out['softname'] = $_GET['softname'];
	}
	if($_GET['stop'] == 1){
		$tplObj -> out['stop'] = 1;
	}		
	$tplObj -> out['types'] = $_GET['types'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	//用户信息
	$userinfo = get_user_info_new($uid,$active_id,"{$prefix}",'one_dollar_userinfo');
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];	
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['prefix'] = $prefix;	
	$tplObj -> out['sid'] = $sid;	

	$tplObj -> display("lottery/{$prefix}/userinfo.html");	
}
