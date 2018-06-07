<?php
	include_once(dirname(realpath(__FILE__)).'/init.php');
	$username = $_POST['u_name'];
	$new_password = $_POST['new_password'];
	$id = $_POST['u_id'];
	$verify_password = $_POST['verify_password'];
	$message = array('status' => 0);
	if($new_password != $verify_password){
		$message['status'] = 0;
		$message['info'] = '两次密码不一致';
		echo json_encode($message);
		exit();
	}
	
	$verify_arr = $user_logic->get_user_info_dev($username);
	$uid = $verify_arr['id'];
	if($id != $uid){
		$message['status'] = 0;
		$message['info'] = '用户不存在';
		echo json_encode($message);
		exit();
	}
	if(!$_POST['str']) exit(json_encode(array('message' => 0,'info' => '参数错误')));
	
	$obj = new GoModel();
	$option = array(
			'table' => 'pu_developer',
			'where' => array(
			'dev_id' => mysql_escape_string($uid),
		),
	);
	$rs = $obj->findOne($option);
	$verify_code = md5($rs['dev_id'].$rs['email'].$rs['mobile'].$rs['verify_email'].$rs['verify_mobile'].$rs['verify_time']);
	if($verify_code != $_POST['str']){
		$message['status'] = 0;
		$message['info'] = '验证失败';
		echo json_encode($message);
		exit();
	}
	
	$obj = new GoModel();
	//修改密码,开始
	$option = array(
			'table' => 'pu_user',
			'where' => array(
			'userid' => $uid,
			'status' => 1,
		),
	);
	$rs_user = $obj->findOne($option);
	
	if(!$rs_user) {
			$message['status'] = 0;
			$message['info'] = '用户不存在';
			echo json_encode($message);
			exit();
	}
	//修改论坛密码
	$auth_json = $user_logic -> get_auth($uid);
	$auth  = json_decode($auth_json,true);
	$str_auth = $auth['msg'];
	
	$rs = $user_logic->change_pwd($rs_user['user_name'],$verify_password,$rs_user['userid'],$str_auth);
	

	$rs = json_decode($rs, TRUE);

	//日志,开始
	if(substr(strtolower(PHP_OS),0,3)!='win') {
		$chgpwd_log = LOG.date('Y-m-d').'/chgpwd.log';
	} else {
		$chgpwd_log = 'e:/chgpwd.log';
	}
	if(!is_dir(dirname($chgpwd_log))) mkdir(dirname($chgpwd_log),0777,true);
	file_put_contents($chgpwd_log,"{$rs_user['userid']}|{$rs_user['user_name']}|{$rs['error']}|{$rs['msg']}|".date('Y-m-d H:i:s')."\n",FILE_APPEND);
	//日志,结束
	if($rs['error'] < 0) {// 更改论坛密码
		//更改密码失败
		$message['status'] = 0;
		$message['info'] = '更改密码失败';
		echo json_encode($message);
	}else{
		$message['status'] = 1;
		//开始pu_user,pu_developer更新
		$data = array();
		$data['user_password'] = md5($new_password);
		$data['__user_table'] = 'pu_user';
		$ret = $obj->update(array('user_name' => $rs_user['user_name']), $data, 'master');
		$data = array();
		$data['verify_email'] = '';
		$data['verify_mobile'] = '';
		$data['verify_time'] = 0;
		$data['__user_table'] = 'pu_developer';
		$obj->update(array('dev_id' => $uid), $data, 'master');

		$user_ip = ($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
		$dev_log = array('user_ip'=>$user_ip,'id'=>$uid,'record_type'=>12,'get_type'=>'pwd_chg');
		//pu_dev_log($dev_log); //写日志 
		echo json_encode($message);
		exit;
	}
?>
