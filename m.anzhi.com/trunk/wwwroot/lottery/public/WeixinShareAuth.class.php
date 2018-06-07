<?php

include_once (dirname(realpath(__FILE__)).'/../../init.php');

class WeixinShareAuth {
	private $appid = 'wxb3dfed519f72089b';
	private $redis;
	private $rkey_auth;
	
	function __construct($appid = '') {
		if (!empty($appid)) {
			$this->appid = $appid;
		}
		$this->rkey_auth = 'activity:h5:weixin_share:auth:'.$this->appid;
		$config = load_config('lottery_cache/redis',"lottery");
		if ($config) {
			$this->redis = new GoRedisCacheAdapter($config);
		} else {
			$this->redis = GoCache::getCacheAdapter('redis');
		}
	}
	
	function get_config($url_param = '') {
		$timestamp = time();
		$nonceStr = $this->getRandChar(32);
		$signature = '';
		
		// 获得token
		$token = $this->redis->gethash($this->rkey_auth, 'token');
		if (empty($token)) {
			// 出错了
			return false;
		}
		$ticket = $this->redis->gethash($this->rkey_auth, 'ticket');
		if (empty($ticket)) {
			// 出错了
			return false;
		}
		// 获得当前请求的url+param
		if (empty($url_param)) {
			$url_param = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}
		// 获得签名
		$sign_ori = "jsapi_ticket={$ticket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url_param}";
		$signature = sha1($sign_ori);
		$wx_share_config = array(
			'appId' => $this->appid,
			'timestamp' => $timestamp,
			'nonceStr' => $nonceStr,
			'signature' => $signature
		);
		return $wx_share_config;
	}
	
	function getRandChar($length){
		$str = '';
		$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($strPol)-1;

		for($i=0;$i<$length;$i++){
			$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		}

		return $str;
	}
}