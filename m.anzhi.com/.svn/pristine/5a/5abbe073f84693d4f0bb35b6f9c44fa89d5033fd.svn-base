<?php

include_once (dirname(realpath(__FILE__)).'/double11_promotion_init_page.php');

// 获取红包抽到的抽奖数
$redpacket_num = $redis->gethash($actsid, 'redpacket_num');
$redpacket_num = $redpacket_num ? $redpacket_num : 0;

$tplObj->out['redpacket_num'] = $redpacket_num;

// 用户今天是否分享过
$share_date = $redis->gethash($actsid, 'share_date');
$is_shared = 0;
if ($share_date == $today) {
	$is_shared = 1;
}
$tplObj->out['is_shared'] = $is_shared;
$tplObj->display("double11_promotion_share.html");