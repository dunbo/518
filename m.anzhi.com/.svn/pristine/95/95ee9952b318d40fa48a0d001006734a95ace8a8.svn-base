<?php
include_once ('./fun.php');
session_begin($sid);
$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/hszz_sign/index.php?".$build_query;

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
	$time = time();	
	$data = array(
		'uid' => $uid,
		'aid' => $active_id,
		'username' => $_SESSION['USER_NAME'],
		'phone' => $_POST['mobile_phone'],
		'contact_name' => $_POST['lxname'],
		'address' => $_POST['address'],
	);
	$ret = add_user($prefix,$data,$time);
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
			'key' => 'user_info'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));			
		exit(json_encode(array('code'=>1,'msg'=>'成功')));
	}else{
		exit(json_encode(array('code'=>0,'msg'=>'失败')));
	}
}else{
	if($_GET['types'] == 1){
		//我的奖品
		// global $redis;	
		 // $redis -> rpop("{$prefix}:{$active_id}_draw_award:{$uid}");die;
		 // $redis -> setlist("{$prefix}:{$active_id}_draw_award:{$uid}")
		$kind_award_list = get_user_kind_award_new($uid,$active_id,"{$prefix}",'royal_war_draw_award');
		// echo "<pre>";var_dump($kind_award_list);die;
		$hign_award_list=array();
		$low_award_list=array();
		foreach($kind_award_list as $k=>$v){
			if($v['pid']<3){
				$hign_award_list[]=$v;
			}else{
				$low_award_list[]=$v;
			}
		}
		$tplObj -> out['kind_award_arr'] = $hign_award_list;
		// $kind_award_gift = get_user_kind_gift_new($uid,$active_id,"{$prefix}","royal_war_draw_gift");
		$tplObj -> out['gift_prize_arr'] = $low_award_list;		
	}else if($_GET['types'] == 2){
		//恭喜中奖
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
	$userinfo = get_user_info_new($uid,$active_id,"{$prefix}","royal_war_draw_userinfo");
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];	
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['sid'] = $sid;	
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
	if($_GET['types'] == 1){
		$tplObj -> display("lottery/hszz_sign/my_prize.html");	
	}else if($_GET['types'] == 3){
		$tplObj -> display("lottery/hszz_sign/userinfo.html");	
	}
}
