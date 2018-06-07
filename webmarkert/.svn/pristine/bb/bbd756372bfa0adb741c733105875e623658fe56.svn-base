<?php
include_once(dirname(realpath(__FILE__)).'/init.php');

if($_GET['do']=='send') {
	if(!preg_match('/^1[0-9]{10}$/',$_GET['mobile'])) {
		exit('2');	//手机号格式错误
	} else {
		$content = "下载安智市场最新版，尽享精彩应用珍馐盛宴，请点击 http://m.anzhi.com/download ";
		$ret = sms_send(array('phone'=>$_GET['mobile'], 'content'=>$content));
		$arr = json_decode($ret['ret'], TRUE);
		if($ret['http_code']!=200) {
			exit('3');					//通讯失败
		} else if(!$arr) {
			exit('4');					//返回数据异常
		} else if($arr['code']!=0) {
			exit($arr['msg']);			//返回错误
		} else {
			exit('1');					//发送成功
		}
	}
}

function sms_send($vals) {
	if(preg_match('/^192\.168\.0/i',$_SERVER['SERVER_ADDR']) || in_array($_SERVER['SERVER_ADDR'],array('127.0.0.1','114.247.222.131'))) {
		$url = 'http://192.168.0.74:91/service.php?do=sendSms';		//测试环境短信发送地址
		$host = 'Host: localhost';
	} else {
		$url = 'http://118.26.224.18/service.php?do=sendSms';		//正式环境短信发送地址
		$host = 'Host: smsapi.goapk.com';
	}
	$url .= '&key=56521dd7d306a0cb0a37a186200351f0&rand='.microtime(true);

	$res = curl_init();
	curl_setopt($res, CURLOPT_URL, $url);
	curl_setopt($res, CURLOPT_TIMEOUT, 10);
	curl_setopt($res, CURLOPT_HTTPHEADER, array($host));
	curl_setopt($res, CURLOPT_POST, true);
	curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
	curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($res);
	$http_code = curl_getinfo($res,CURLINFO_HTTP_CODE);
	$errno = curl_errno($res);
	$error = curl_error($res);
	curl_close($res);

	return array(
		'ret' => $result,
		'http_code' => $http_code,
		'errno' => $errno,
		'error' => $error,
	);
}
