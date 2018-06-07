<?php

include_once (dirname(realpath(__FILE__)).'/init.php');
	return 1;
	echo 1;
	exit;
	$telphone = $_GET['telphone'];
	$aid = $_GET['aid'];
	$sid = $_GET['sid'];
	$data = array(
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $sid,
		'aid' => $aid,
		'mobile_phone' => $telphone,
		'time' => time()
	);
	
	permanentlog('adactivity_'.$aid.'.log', json_encode($data));
	
	