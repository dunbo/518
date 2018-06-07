<?php
   include_once('../../init.php');
   $ip = $_GET['ip'];
   $sid = $_GET['sid']; 
   $username = $_GET['username'];
   $mobile_phone = $_GET['mobile'];
   load_helper('utiltool');
   $data = array(
	'activity_id' => 3,
	'activity_name' => '天朝小将感恩大回馈',
	'ip' => $ip,
	'sid' => $sid,
    'username' => $username,
	'mobile_phone' => $mobile_phone,
	'time' => time(),
	);
	#file_put_contents("/tmp/chinayong.log",json_encode($data)."\r\n",FILE_APPEND);
   permanentlog('chinayong.log', json_encode($data));
   
