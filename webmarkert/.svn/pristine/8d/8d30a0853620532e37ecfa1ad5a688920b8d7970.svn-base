<?php
	include_once(dirname(realpath(__FILE__)).'/init.php');
	include_once(dirname(realpath(__FILE__)).'/function.php');
	include_once(GO_APP_ROOT. '/../newgomarket.goapk.com/helper/discuz.helper.php');

	$user = $_POST['username'];
	$sel_way = $_POST['sel_way'];
	$sel_email = $_POST['sel_email'];
	//$email = $_POST['email'];
	$verify_code = $_POST['verify_code'];

	$verify_arr = $user_logic -> get_user_info_dev($user);
	$message = array('status' => 0);
	if($verify_arr == false)
	{
			$message['status'] = 0;
			$message['info'] = '帐号不存在';
			echo json_encode($message);
	}elseif(strtolower($user)=='admin'){
			$message['status'] = 0;
			$message['info'] = '用户名不合法';
			echo json_encode($message);
	}else{

		if($sel_way =='sel_phone'){
			$message = array('status' => 0);
			if($verify_arr == false){
			$message['status'] = 0;
			$message['info'] = '帐号不存在';
			//return false;
		} else {
			$obj = new GoModel();
			$option = array(
					'table' => 'pu_developer',
					'where' => array(
					'dev_id' => mysql_escape_string($verify_arr['id']),
					//'status' => 0,
				),
			);
			$rs = $obj->findOne($option);
			if($verify_code == ''){
				$message['status'] = 0;
				$message['info'] = '验证码为空';

			}else if($verify_code!=''){
				$dev_id = $verify_arr['id'];
				$mobile_code = $verify_code;
				$mobile_result = $user_logic->check_mobile_code($dev_id,$mobile_code);
				if($mobile_result['error']=='0'){
					$message['status'] = 1;
					$message['info'] = 'ok';
					$message['str'] = md5($rs['dev_id'].$rs['email'].$rs['mobile'].$rs['verify_email'].$rs['verify_mobile'].$rs['verify_time']);
					$message['uid'] = $verify_arr['id'];
					$message['type'] = 3;
				}else{
					$message['status'] = 0;
					$message['info'] = $mobile_result['msg'];
				}
			}
		}
		echo json_encode($message);
	}else if($sel_way=='sel_email'){
		$model = new GoModel();
		$msec = microtime(true);
		$time = time();
		if($time-$_SESSION['texttime']<180){
			
			$result['error'] = 600;
			echo json_encode($result);
			exit;
		}
	

		$rand = '';
		$rand = rand_code_md5();
		if($verify_arr == false){
			$message['status'] = 0;
			$message['info'] = '帐号不存在';
		}else if($sel_email =='dev' && $verify_arr['dev_email_verify'] == 1){
			$email = $verify_arr['dev_email'];
			$http_url = "http://www.anzhi.com/change_pwd.php?code={$rand}&id={$verify_arr['id']}&r={$msec}&verify_type=1";
		}else if($sel_email =='bbs'){
			$email = $verify_arr['bbs_email'];
			//$auth = $user_logic	-> get_auth($verify_arr['id']);
			$http_url = "http://www.anzhi.com/change_pwd.php?id={$verify_arr['id']}&r={$msec}&code={$rand}&verify_type=2";
		}else if($verify_arr['bbs_email']!=''){
			//$auth = $user_logic	-> get_auth($verify_arr['id']); 
			if($verify_arr['dev_email_verified']!=0){
				$email = $verify_arr['dev_email'];
				$http_url = "http://www.anzhi.com/change_pwd.php?code={$rand}&id={$verify_arr['id']}&r={$msec}&verify_type=1";
			}else{
				$email = $verify_arr['bbs_email'];
				$http_url = "http://www.anzhi.com/change_pwd.php?id={$verify_arr['id']}&r={$msec}&code={$rand}&verify_type=2";
				}
		}else{
			$message['status'] = 0;
			$message['info'] = '邮箱有问题';		
			//return false;
		}
		$option = array(
					'table' => 'pu_developer',
					'where' => array(
					'dev_id' => mysql_escape_string($verify_arr['id']),
					//'status' => 0,
				),
			);
		$rs = $model->findOne($option);
		if($rs!='')
		{
			$data = array(
				'verify_time' => time(),
				'verify_email'=>$rand,
				'__user_table' => 'pu_developer',
				);
				$where = array(
					'dev_id' => $verify_arr['id']
				);
			$model->update($where, $data);

		}else{
			$data = array(
                    'dev_id' => $verify_arr['id'],
                    'email'  => $email,
                    'status' => 1,		//待审核
                    'dev_name' =>$user,
                    'register_time' => time(),
                    'complete_time' => time(),
                    'verify_time' => time(),
                    'last_time' => time(),
                    'lastlogin' => time(),
                    'lastlogin2' => time(),
                    'verify_email'=>$rand,
                    'reg_ip' => $_SERVER['REMOTE_ADDR'],
                    '__user_table' => 'pu_developer',
                    'ver' => 2 //新系统用户!!
          	  );
           	 $model->insert($data);			
		}
           	 $data_user = array(
					'userid' => $verify_arr['id'],
					'user_name' => trim($user),
					'user_password' =>'',
					'imei' => '',
					'last_time' => time(),
					'status' => 1,
					'last_ip' => ip2long($_SERVER['SERVER_ADDR']),
					'__user_table' => 'pu_user',
				);
			$model->insert($data_user);
		$msg = array(
			'getpasswd_account_notmatch' => '用户名/邮箱不匹配。',
			'getpasswd_account_invalid' => '用户名无效。',
		);
		$title = '安智密码找回';
    	$email_time = date('Y-m-d',$time);
		$content = "亲爱的安智用户，<br /><br />您提交了密码更改请求，请点击以下链接重置您的密码：<br /><a href={$http_url}  target=_blank>{$http_url}</a><br />(如果您无法点击此链接，请将它复制到浏览器地址栏后访问)<br />为了保证您帐号的安全，该链接有效期为24小时，并且点击一次后失效！<br />
<br />如有问题，请与安智客服联系<br />安智管理团队敬上<br /><a href=http://www.anzhi.com>http://www.anzhi.com</a><br />日期：{$email_time}<br />(这是一封自动产生的E-mail,请勿回复)";

		$result = send_webmarket_mail($email,$user,$title,$content);
		if($result['error'] ==0){
			$_SESSION['texttime'] = time();
		}
	/*	$message = array('status' => 0);
		if ($result['error'] > 0){
			$message['status'] = 1;
			$message['info'] = '密码找回信息已发送的您指定的邮箱！';

		}elseif(isset($msg[$result])){
			$message['info'] = $msg[$result]; 
		}else{
			$message['info'] = '网络出现问题请稍后再试！';
		}*/
		echo json_encode($result);
	}

}
