<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
$bbs_username = $_POST['user_name'];
if(preg_match('/^\d+$/',$bbs_username)){
	echo "no";
}else{
	$call_result= $user_logic->check_user($bbs_username);
	$result = json_decode($call_result);
	if($result[0] > 1){
	 exit(123);
	}
	exit(0);
}