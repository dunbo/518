<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
$tencent_softid = $_GET['tencent_softid'];
$wdj_softid = $_GET['wdj_softid'];
//获取IP
$cip = onlineip();
$user_name = $_SESSION['user_data']['user_name'];
$userid = $_SESSION['user_data']['userid'];
//写入日志
if($tencent_softid){
	pu_load_model_obj('pu_log', array('logfile' => 'tencent_yj.log', 'message' => json_encode(	array(
			'softid' => $tencent_softid,
			'ip' => $cip,
			'time' => time(),
			'user_name' => $user_name,
			'userid' => $userid
		))
	))->save_data_info();
}

if($wdj_softid){
	pu_load_model_obj('pu_log', array('logfile' => 'wdj_yj.log', 'message' => json_encode(	array(
			'softid' => $wdj_softid,
			'ip' => $cip,
			'time' => time(),
			'user_name' => $user_name,
			'userid' => $userid
		))
	))->save_data_info();
}

return 1;