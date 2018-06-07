<?php
include_once ('./fun.php');
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
session_begin($sid);
$build_query = http_build_query($_GET);
$url = "http://promotion.anzhi.com/lottery/xy2/yd_index.php?".$build_query;

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
	$ret = add_user($data,$time,"christmas","integral_userinfo");
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
	$tplObj -> out['pid'] = $_GET['pid'];	
	$tplObj -> out['package'] = $_GET['package'];	
	$tplObj -> out['softname'] = $_GET['softname'];	
	$tplObj -> out['gift_num'] = $_GET['gift_num'];	
	$tplObj -> out['prizename'] = $_GET['prizename'];	
	$tplObj -> out['lfrom'] = $_GET['lfrom'];	

        $nowday = date('m月d日',time());
	$tplObj -> out['nowday'] = $nowday;


	$tplObj -> out['static_url'] = $configs['static_url'];
	//用户信息
	$userinfo = get_user_info_new($uid,$active_id,'yd','xy2_draw_userinfo');
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];	
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['sid'] = $sid;	
        $tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
	$tplObj -> display('lottery/xy2/yd_myprize.html');
}
