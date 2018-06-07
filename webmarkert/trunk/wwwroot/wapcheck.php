<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
$type = (int) $_GET['type'];
$ip = onlineip();
$rf = $_SERVER['HTTP_REFERER'];
$dltime = time();
$log = array(
	'IP'=>$ip,
	'USERID'=>isset($_SESSION['user_data']['userid'])? $_SESSION['user_data']['userid'] : GO_UID_DEFAULT,
	'REFER'=>$rf,
	'TIME'=>$dltime,
);
if ($type == 1) {//点击跳转wap页
	$log['KEY']="HREF";
	$log['TYPE']=1;
	pu_load_model_obj('pu_log', array('logfile' => 'wapcheck.log', 'message' => json_encode($log)))->save_data_info();
	echo 1;
} elseif ($type == 2) {//点击跳转www页
	$log['KEY']="HREF";
	$log['TYPE']=2;
	pu_load_model_obj('pu_log', array('logfile' => 'wapcheck.log', 'message' => json_encode($log)))->save_data_info();
	echo 2;
} elseif ($type == 3) {//点击下载
	// $softid = $newanzhi['ID'];
	
	$log['KEY']="MARKET_DOWNLOAD";
	// $log['SOFTID']=$softid;
	pu_load_model_obj('pu_log', array('logfile' => 'wapcheck.log', 'message' => json_encode($log)))->save_data_info();
	// echo json_encode(array('id'=>3,'softid'=>$softid));
	echo 3;

} else {
	exit();
}