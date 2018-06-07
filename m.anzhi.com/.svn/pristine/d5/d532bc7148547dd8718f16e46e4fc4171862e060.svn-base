<?php
include_once ('./fun.php');

if($_GET['is_sign_rule'] == 1){
	$tplObj -> out['prefix'] = $prefix;	
	$tplObj -> out['static_url'] = $configs['static_url'];	
	$tpl = "{$prefix}/sign_rule.html";
	$tplObj -> display($tpl);
	exit;
}
$where = array(
	'mid'=>$m_arr['id'],
	'status'=>1,
	'type' => array('exp','!=6')
);
if($_POST['did']){
	$where['did'] = $_POST['did'];
}else{
	$where['cid'] = $_POST['cid'];
}
//奖品详情日志
$log_data = array(
	"imsi" => $_SESSION['USER_IMSI'],
	"device_id" => $_SESSION['DEVICEID'],
	"mac" => $_SESSION['MAC'],				
	'uid'=>$uid,
	'sid' => $sid,	
	'username' => $_SESSION['USER_NAME'],
	'time' => $time,
	"did"	=>	$_POST['did'] ? $_POST['did'] : 0,
	"cid"	=>	$_POST['cid'] ? $_POST['cid'] : 0,
	"mid"	=>	$m_arr['id'],
	'key' => 'prize_info'
);
permanentlog($log_key, json_encode($log_data));	
$option = array(
	'where' => $where,
	'table' => 'qd_draw_prize',
	'order' => 'type asc',
//	'cache_time' => 30*60,
	'limit' => 6
);
$ret_arr = $model->findAll($option,'lottery/lottery');	
exit(json_encode($ret_arr));