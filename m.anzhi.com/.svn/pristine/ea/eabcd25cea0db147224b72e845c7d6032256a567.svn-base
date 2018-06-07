<?php

/*
** 分享首页获得红包
*/

include_once (dirname(realpath(__FILE__)).'/double11_promotion_init.php');

$tplObj -> out['aid'] = $aid;
$tplObj -> out['actsid'] = $actsid;

// 从缓存里获得，看是否已取得过红包
$redpacket_num = $redis->gethash($actsid, 'redpacket_num');
if (empty($redpacket_num)) {
	// 随机出1-5一个红包数
	$redpacket_num = mt_rand(1,5);
	if ($_SERVER['SERVER_ADDR'] == '118.26.203.23') {
		$jump_url_host = 'http://118.26.203.23';
	} else {
		$jump_url_host = 'http://m.anzhi.com';
	}
	$jump_url = $jump_url_host . '/lottery/double11_lottery.php';
	
	$data = array(
		'url' => $jump_url,
		'redpacket_num' => $redpacket_num,
	);
	
	$redis->sethash($actsid, $data, $r_cache_time);
}

echo $redpacket_num;
exit;
