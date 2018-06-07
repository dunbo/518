<?php
function sendVerifyEmail($username, $email, $uid) {
	$code = generateVerifyCode($uid, $username, $email);
	if (!$code) {
		return false;
	}
	$url = "http://www.anzhi.com/forget_password.php?code=$code&uid=$uid";
	//$email_cont = "找回密码链接为：\r\n$url";
	$email_cont = '亲爱的安智用户，<br><br>您提交了密码更改请求，请点击以下链接重置您的密码：<br><br><a href="' . $url . '" target="_blank">' . $url . '</a><br><br>(如果您无法点击此链接，请将它复制到浏览器地址栏后访问)<br>为了保证您帐号的安全，该链接有效期为24小时，并且点击一次后失效！<br><br>如有问题，请与安智客服联系<br>安智管理团队敬上<br>http://www.anzhi.com<br>日期：2014-08-04<br>(这是一封自动产生的E-mail,请勿回复)';
	$rs = __http_post_email(array('email' => $email, 'name' => $username, 'subject' => '修改密码', 'content' => $email_cont));
	if ($rs['http_code'] == 200) {
		return true;
	} else {
		return false;
	}
}

function checkVerifyCode($uid, $code) {
	$model = new GoModel();
	$option = array(
		'table' => 'pu_user_forget_password',
		'where' => array(
			'uid' => $uid,
			'code' => $code,
		),
	);
	$info = $model->findOne($option);
	if (empty($info)) {
		return false;
	} else {
		if ($info['code_status'] != 1 || (time() - $info['code_time'] > 86400)) {
			return false;
		} else {
			return true;
		}
	}
}

function useVerifyCode($uid, $code) {
	$model = new GoModel();
	$where = array(
		'uid' => $uid,
		'code' => $code,
	);
	$data = array(
		'__user_table' => 'pu_user_forget_password',
		'code_status' => 0,
	);
	$model->update($where, $data);
}

function generateVerifyCode($uid, $username, $email) {
	$model = new GoModel();
	$now = time();
	$option = array(
		'table' => 'pu_user_forget_password',
		'where' => array(
			'uid' => $uid,
			'code_status' => 1,
			'code_time' => array('exp', ">= $now - 86400"),
		),
		'field' => 'code',
	);
	$res = $model->findOne($option);
	if (empty($res)) {
		$code = md5(uniqid());
		$sql = "INSERT INTO pu_user_forget_password(uid, username, email, code, code_time, code_status)
			VALUE ($uid, '$username', '$email', '$code', $now, 1)
			ON DUPLICATE KEY UPDATE code='$code', code_time=$now, username='$username', email='$email', code_status=1";
		$res = $model->query($sql);
		if ($res == false) {
			return false;
		} else {
			return $code;
		}
	} else {
		return $res['code'];
	}
}
function __http_post_email($vals) {
	if (preg_match('/^192\.168\.0/i', $_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR'] == '10.0.2.15' || $_SERVER['SERVER_ADDR'] == '114.247.222.131') {
		$url = 'http://42.62.4.183/service.php';
		$host = 'Host: mail.goapk.com';
	} else {
		$url = 'http://192.168.1.143/service.php';
		$host = 'Host: mail.goapk.com';
	}

	$url .= '?key=f3778b2d59c276233de4f73b2ebf46ea';

	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_TIMEOUT, 5);
	curl_setopt($res, CURLOPT_HTTPHEADER, array($host));
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
	curl_close($res);

	$log_file = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN' ? 'e:/email.log' : '/data/att/permanentlog/sendmail/' . date('Y-m-d') . '/email.log';
	if (!is_dir(dirname($log_file)))
		mkdir(dirname($log_file), 0777, true);
	file_put_contents($log_file, "post|{$url}|{$host}|{$vals['email']}|{$vals['subject']}|{$vals['content']}|{$http_code}|" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

	return array(
		'ret' => $result,
		'http_code' => $http_code,
	);
}
?>
