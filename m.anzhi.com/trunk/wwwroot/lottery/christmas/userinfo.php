<?php
include_once ('./fun.php');
session_begin($sid);
$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/christmas/index.php?".$build_query;
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
	$ret = add_user_new($data,$time,"christmas","integral_userinfo");
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
		$kind_award_list = get_user_lottery_record($uid,$active_id);
		$tplObj -> out['kind_award_arr'] = $kind_award_list;
		$integral_kind_list = get_user_integral_kind_record($uid,$active_id);
		$tplObj -> out['integral_kind_award_arr'] = $integral_kind_list;
		$kind_award_gift = get_user_kind_gift_new($uid,$active_id,"christmas","integral_kind_gift");	
		$tplObj -> out['gift_prize_arr'] = $kind_award_gift;		
	}else if($_GET['types'] == 2){
		//恭喜中奖
		$tplObj -> out['now'] = date('Y-m-d');
		$tplObj -> out['prizename'] = $_GET['prizename'];
		$tplObj -> out['package'] = $_GET['package'];
		$tplObj -> out['gift_num'] = $_GET['gift_num'];
		$tplObj -> out['softname'] = $_GET['softname'];
	}else if($_GET['types'] == 4){
		//恭喜兑换成功
		$tplObj -> out['now'] = date('Y-m-d');
		$tplObj -> out['prizename'] = $_GET['prizename'];
	}
	if($_GET['stop'] == 1){
		$tplObj -> out['stop'] = 1;
	}		
	$tplObj -> out['types'] = $_GET['types'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];	
	//用户信息
	$userinfo = get_user_info_new($uid,$active_id,'christmas','integral_userinfo');
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];	
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['sid'] = $sid;	
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
	$tplObj -> display('lottery/christmas/userinfo.html');	
}
