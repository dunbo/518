<?php

	include_once (dirname(realpath(__FILE__)).'/init.php');
	
	
	$name = $_GET['name'];
	$phone = $_GET['phone'];
	$package_str = $_GET['package_arr'];
	$package_arr = explode(',',$package_str);
	$aid = $_GET['aid'];
	$sid = $_GET['sid'];
	$model = new GoModel();
	$opts = array(
		'table' => 'sj_activity',
		'where' => array(
			'id' => $aid	
		)
	);
	$activity = $model->findOne($opts);
	$data = array(
		'activity_id' => $aid,
		'activity_name' => $activity['name'],
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $sid,
		'username' => $name,
		'mobile_phone' => $phone,
		'time' => time()
	);

	permanentlog('activity_'.$aid.'.log', json_encode($data));
	
	
	foreach($package_arr as $key => $val){
		if($val){
			$softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $val,'EXTRA_OPTION_FIELD' => array('min_firmware','max_firmware')));
			if($softinfo){
				$softinfo_arr[] = $softinfo;
			}else{
				$softinfo_arr[] = 0;
			}
		}
	}
	echo json_encode($softinfo_arr);
	
