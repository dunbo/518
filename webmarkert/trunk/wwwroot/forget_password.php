<?php
include_once(dirname(realpath(__FILE__)).'/init.php');
load_helper('password');
load_helper('ucenter');
if (empty($_GET['uid']) || empty($_GET['code'])) {
	$tplObj->out['act'] = 'error';
	$tplObj->out['message'] = '该链接无效或已过期';
}
$uid = $_GET['uid'];
$code = $_GET['code'];
if (checkVerifyCode($uid, $code)) {
	if ($_GET['act'] == 'submit') {
		$tplObj->out['act'] = 'submit';
		$firstPass = $_POST['password'];
		$secondPass = $_POST['password_c'];
		if (!empty($firstPass) && $firstPass == $secondPass) {
			if (strlen($firstPass) < 6 || strlen($firstPass) > 16) {
				$tplObj->out['message'] = '请输入6-16位的密码';
			} else {
				$res = uc_resetPassword($uid, $firstPass, time());
				if ($res['success']) {
					useVerifyCode($uid, $code);
					$tplObj->out['message'] = '密码修改成功';
				} else {
					$tplObj->out['message'] = '密码修改失败，请重试';
				}
			}
		} else {
			$tplObj->out['message'] = '两次密码输入不一致';
		}
	} else {
		$tplObj->out['act'] = 'show';
	}
} else {
	$tplObj->out['act'] = 'error';
	$tplObj->out['message'] = '该链接无效或已过期';
}
$tplObj->display ( "forget_password.html" );
