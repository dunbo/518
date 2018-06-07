<?php
	exit('deny access');
	include_once(dirname(realpath(__FILE__)).'/init.php');
	if ($_SERVER['REQUEST_METHOD'] == 'GET' || $_GET['act'] != 'submit') {
	    $tplObj->display('register.html');
	    return ;
	}
	
	list($error, $user_data) = $user_logic->register_user($_POST);
	if ($error) {
	    $tplObj->out['error'] = $error;
		if(isset($user_data['message'])){
			include_once(dirname(realpath(__FILE__)).'/error_msg.php');
			if($message[$user_data['message']]){
				$tplObj->out['message'] = $message[$user_data['message']];
			}else{
				$tplObj->out['message'] = '建议您去安智论坛注册用户！';
			}
			$tplObj->out['is_msg'] = 1;
		}
	    $_GET['referer'] = $_POST['referer'];
	    $tplObj->display('register.html');
	    return ;
	} else {
		$user = array(
			'USER_NAME' => $user_data['user_name'],
			'USER_ID' => $user_data['userid'],
			'time' => time(),
			'email' => $user_data['email'],
		);
		include_once('../tools/functions.php');
		$encrypt = rc4_encode($user);
		$cross_domain = preg_replace('/^([a-z0-9_-]+\.)*([a-z0-9_-]+\.[a-z0-9_-]+)$/', '\2', $_SERVER['HTTP_HOST']);
		
		setcookie('__gosession', $encrypt, 0, '/', $cross_domain);
	    $_SESSION['user_data'] = $user_data;
	    header('location: '.get_login_register_referer($_POST['referer']));
	    exit;
	}
