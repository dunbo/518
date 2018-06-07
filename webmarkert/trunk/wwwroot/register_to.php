<?php
	include_once(dirname(realpath(__FILE__)).'/init.php');
	include_once(dirname(realpath(__FILE__)).'/function.php');
	$username = $_POST['user_name'];
	if(preg_match('/^\d+$/',$username)){
		echo "no";
		exit;
	}elseif(preg_match('/[@]+/',$username)){
		exit("error_code");
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
		$title = '安智账号注册通知';
	    $email_time = date('Y-m-d');
		$content = "亲爱的安智用户，<br /><br />欢迎来到安智，恭喜您成功注册安智账号！<br />请牢记您的账号以便于享受更多权限<br /><br />如有问题，请与安智客服联系<br />安智管理团队敬上<br /><a href='http://www.anzhi.com'>http://www.anzhi.com</a><br />日期：$email_time<br />(这是一封自动产生的E-mail,请勿回复)";
		$res = send_webmarket_mail($user_data['email'],$user_data['user_name'],$title,$content);
		if ($res['error'] > 0){
			$message['status'] = 1;
			$message['info'] = '密码找回信息已发送的您指定的邮箱！';

		}
		echo json_encode($res);
	    exit;
	}
