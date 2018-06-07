<?php

include_once (dirname(realpath(__FILE__)).'/double11_init_page.php');

// 检查用户今天是否分享过
$share_date = $redis->get($rkey_imsi_share_info);
$is_shared = 0;
if ($share_date == $today) {
	$is_shared = 1;
}
$tplObj->out['is_shared'] = $is_shared;

// 获得用户的红包数
$redpacket_num = $redis->get($rkey_imsi_open_get_info);
$tplObj->out['redpacket_num'] = $redpacket_num ? $redpacket_num : 0;

$tplObj->display("double11_share.html");