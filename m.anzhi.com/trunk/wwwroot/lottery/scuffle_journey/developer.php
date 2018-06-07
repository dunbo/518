<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$model = new GoModel();
$active_id = 'sd_developer';
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
session_begin();

if($configs['is_test'] == 1 ){
	$time = get_now_time();
}else {
	$time = time();
}

//获取host
$activity_host = $configs['activity_url'];
$day  = date("Ymd",$time);

if($_POST){
	if($day > 20161226){
		$url = $activity_host."/lottery/scuffle_journey/developer.php";
		header("Location: {$url}");
	}
	$model = new  GoModel();
	$new_data = array(
			'ip'				=>	$_SERVER['REMOTE_ADDR'],
			"device_id"			=>	$_SESSION['DEVICEID'],
			'company_name'		=>	$_POST['company_name'],
			'product_name'		=>	$_POST['product_name'],
			'lxname'			=>	$_POST['lxname'],
			'phone'				=>	$_POST['mobile_phone'],
			'qq'				=>	$_POST['qq'],
			'email'				=>	$_POST['email'],
			'money'				=>	$_POST['money'],
			'create_tm'			=>	$time,
			'activity_type'		=>	$active_id,
			'desc'				=>	'开发者圣诞广告促销活动',
			'__user_table'		=>	'temp_activity_userinfo'
	);
	$ret = $model->insert($new_data,'lottery/lottery');
	if($ret) {
		$log_data = array(
				"sid"			=>	$sid,
				"ip"			=>	$_SERVER['REMOTE_ADDR'],
				'company_name'	=>	$_POST['company_name'],
				'product_name'	=>	$_POST['product_name'],
				'lxname'		=>	$_POST['lxname'],
				'mobile_phone'	=>	$_POST['mobile_phone'],
				'qq'			=>	$_POST['qq'],
				'email'			=>	$_POST['email'],
				'money'			=>	$_POST['money'],
				'time'			=>	$time,
				'activity_id'	=>	$active_id,
				'key'			=>	'info_edit'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		exit(json_encode(array('code'=>1,'msg'=>'提交成功')));
	}else {
		exit(json_encode(array('code'=>0,'msg'=>'提交失败')));
	}
}else{
	//日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		'key' => 'show_homepage'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	$tpl = "lottery/scuffle_journey/developer.html";
	$tplObj -> out['imsi'] = $_SESSION['USER_IMSI'];
	$tplObj -> out['aid'] = $active_id;
	if($day == '20161223' || $day == '20161224' || $day == '20161225' ||  $day == '20161226'){
		$is_stop  = 1;
	}else{
		$is_stop  = 0;
	}
	$tplObj -> out['is_stop'] = $is_stop;
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
	$tplObj -> out['share'] = $_GET['share'];
	$tplObj -> out['share_url'] = $share_url;
	$tplObj -> display($tpl);
}


function get_now_time(){
	global $model;
	$option = array(
			'where' => array(
					'status'  => 1,
					'conf_id' => 280
			),
			'table' => 'pu_config',
			'field' => 'configcontent',
	);
	$list = $model->findOne($option);
	return strtotime($list['configcontent']);
}