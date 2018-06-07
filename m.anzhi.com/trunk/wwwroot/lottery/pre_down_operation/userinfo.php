<?php
include_once ('./fun.php');
session_begin($sid);
$build_query = http_build_query($_GET);
$url = $activity_host."/lottery/{$prefix}/index.php?".$build_query;

if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
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
	$ret = add_user_new($data,$time,"{$prefix}","pre_down_operation_userinfo");
    if($ret){
		//用户修改信息日志
		$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"DEVICE_SN" => $_SESSION['DEVICE_SN'],			
			'uid'=>$uid,
			'sid' => $sid,	
			'username' => $_SESSION['USER_NAME'],
			'tel' => $_POST['mobile_phone'],
			'name' => $_POST['lxname'],
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
	$config = get_config($active_id,$_GET['ap_id']);
	$tplObj -> out['img_url'] = getImageHost();
	if($_GET['stop'] == 1){
		$tplObj -> out['stop'] = 1;
		$option = array(
			'where' => array(
				'id' => $active_id,
			),
			'table' => 'sj_activity',
			'field' => 'name,activity_page_id,activity_end_id,end_tm',
			'cache_time' => 30*60
		);
		$activity = $model->findOne($option);		
		$start_config = get_config($active_id,$activity['activity_page_id']);
		$config['title'] = $start_config['title'];
		$config['info_color'] = $start_config['info_color'];
		$config['ranking_no_pic1'] = $start_config['ranking_no_pic1'];
		$config['uppage_color'] = $start_config['uppage_color'];
		$config['ranking_pic1'] = $start_config['ranking_pic1'];
		$config['nextpage_color'] = $start_config['nextpage_color'];				
		$config['nextpage'] = $start_config['nextpage'];		
		
		$config['alert_color'] = $start_config['alert_color'];
		$config['alert_button_color'] = $start_config['alert_button_color'];				
	}		
	//用户信息
	$userinfo = get_user_info_new($uid,$active_id,"{$prefix}","pre_down_operation_userinfo");
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];	
	$tplObj -> out['aid'] = $active_id;	
	$tplObj -> out['sid'] = $sid;	
	$tplObj -> out['prefix'] = $prefix;	
	$tplObj -> out['list'] = $config;
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];	
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];	
	$tplObj -> display("lottery/{$prefix}/userinfo.html");	
}