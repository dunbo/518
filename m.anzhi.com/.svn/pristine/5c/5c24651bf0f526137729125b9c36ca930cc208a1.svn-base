<?php
include_once (dirname(realpath(__FILE__)).'/init.php');

$name = $_GET['name'];
$phone = $_GET['phone'];
$package = $_GET['jsondata'];
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
	'mobile_phone' => $phone,
	'time' => time()
);
if ($name) $data['username'] = $name;


permanentlog('activity_'.$aid.'.log', json_encode($data));
if($_GET['package']){
	$softinfo = gomarket_action('soft.GoGetSoftDetailPackage', array('PACKAGE_NAME' => $_GET['package'],'EXTRA_OPTION_FIELD' => array('min_firmware','max_firmware')));
	echo json_encode($softinfo);
}else{
	echo 1;
	return 1;
	exit;
}
?>
