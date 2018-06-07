<?php
	include_once(dirname(realpath(__FILE__)).'/init.php');
	//ini_set("display_errors", true);
	//error_reporting(E_ALL);
	include_once(GO_APP_ROOT. '/../newgomarket.goapk.com/helper/discuz.helper.php');
	//load_helper('discuz');
	//$username = $_POST['username'];
	//todo check user

	//$mobile = $_POST['mobile'];

	$msg = array(
		'getpasswd_account_notmatch' => '用户名/邮箱不匹配。',
		'getpasswd_account_invalid' => '用户名无效。',
	);
	$result = send_lostpasswd($user, $email, isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '?');
	$message = array('status' => 0);
	
	if ($result == 'getpasswd_send_succeed'){
		$message['status'] = 1;
		$message['info'] = '密码找回信息已发送的您指定的邮箱！';
	}elseif(isset($msg[$result])){
		$message['info'] = $msg[$result]; 
	}else{
		$message['info'] = '网络出现问题请稍后再试！';
	}
	//$tplObj -> out['message'] = $message;
	//$tplObj->display('login.html');
	//echo $message;
	echo json_encode($message);
}