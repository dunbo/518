<?php

// 获得当前请求的url+param
$url_param = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$appid = 'wxb3dfed519f72089b';
$secret = '961e39ad659b7968e2108b67d595a617';
$timestamp = time();
$nonceStr = getRandChar_1(32);
$signature = '';

// 获得token
$token = get_token_1($appid, $secret);

if ($token) {
	// 获得ticket
	$ticket = get_ticket_1($token);
}

if ($ticket) {
	// 获得签名
	$sign_ori = "jsapi_ticket={$ticket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url_param}";
	$signature = sha1($sign_ori);
}

$wx_share_config = array(
	'appId' => $appid,
	'timestamp' => $timestamp,
	'nonceStr' => $nonceStr,
	'signature' => $signature
);

function get_token_1($appid, $secret) {
	global $redis;
	
	$rkey_token = 'defendwar2016:h5:weixin_share_token';
	$times = 0;
	do {
		$token = $redis->get($rkey_token);
		if ($token || $times > 3) {
			break;
		}
		// 请求微信接口
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
		$cmd = "curl -m 30 -s '{$url}'";
		$token_ret = shell_exec($cmd);
		$token_ret = json_decode($token_ret, true);
		$token = $token_ret['access_token'];
		if (!empty($token)) {
			$redis->set($rkey_token, $token, 3600);//一小时
		}
		$times++;
	} while(1);
	
	if (empty($token)) {
		return false;
	}
	
	return $token;
}

function get_ticket_1($token) {
	global $redis;
	
	$rkey_ticket = 'defendwar2016:h5:weixin_share_ticket';
	$times = 0;
	do {
		$ticket = $redis->get($rkey_ticket);
		if ($ticket || $times > 3) {
			break;
		}
		// 请求微信接口
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$token}&type=jsapi";
		$cmd = "curl -m 30 -s '{$url}'";
		$ticket_ret = shell_exec($cmd);
		$ticket_ret = json_decode($ticket_ret, true);
		$ticket = $ticket_ret['ticket'];
		if (!empty($ticket)) {
			$redis->set($rkey_ticket, $ticket, 3600);//一小时
		}
		$times++;
	} while(1);

	if (empty($ticket)) {
		return false;
	}
	
	return $ticket;
}

function getRandChar_1($length){
	$str = '';
	$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	$max = strlen($strPol)-1;

	for($i=0;$i<$length;$i++){
		$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
	}

	return $str;
}