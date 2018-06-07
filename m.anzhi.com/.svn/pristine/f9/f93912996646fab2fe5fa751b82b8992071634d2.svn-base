<?php
/*
** 活动分享页
*/

include_once (dirname(realpath(__FILE__)).'/octoberflight_init_page.php');

// 判断用户当天是否分享过
$last_share_day = $redis->get($rkey_imsi_share_info);
$ever_share = 0;
if ($last_share_day == $today) {
	$ever_share = 1;
}
$tplObj->out['ever_share'] = $ever_share;
$tplObj->display("octoberflight_share.html");