<?php

/*
** 主动刷新微信分享授权redis
*/

include_once (dirname(realpath(__FILE__)).'/init.php');

$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$appid = 'wxb3dfed519f72089b';
$secret = '961e39ad659b7968e2108b67d595a617';

$token = get_token($appid, $secret);
if (!$token) {
	exit;
}

$ticket = get_ticket($token);
if (!$ticket) {
	exit;
}

// 将token、ticket写redis
$auth_map = array(
	'token' => $token,
	'ticket' => $ticket,
);

$rkey = 'activity:h5:weixin_share:auth:'.$appid;
$redis->sethash($rkey, $auth_map, 7200);


function get_token($appid, $secret) {
	$rkey_token = 'defendwar2016:h5:weixin_share_token';
	$times = 0;
	$token = false;
	do {
		if ($token || $times > 3) {
			break;
		}
		// 请求微信接口
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
		$cmd = "curl -m 30 -s '{$url}'";
		$token_ret = shell_exec($cmd);
		$token_ret = json_decode($token_ret, true);
		$token = $token_ret['access_token'];
		$times++;
	} while(1);
	
	if (empty($token)) {
		return false;
	}
	
	return $token;
}

function get_ticket($token) {
	$rkey_ticket = 'defendwar2016:h5:weixin_share_ticket';
	$times = 0;
	$ticket = false;
	do {
		if ($ticket || $times > 3) {
			break;
		}
		// 请求微信接口
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$token}&type=jsapi";
		$cmd = "curl -m 30 -s '{$url}'";
		$ticket_ret = shell_exec($cmd);
		$ticket_ret = json_decode($ticket_ret, true);
		$ticket = $ticket_ret['ticket'];
		$times++;
	} while(1);

	if (empty($ticket)) {
		return false;
	}
	
	return $ticket;
}