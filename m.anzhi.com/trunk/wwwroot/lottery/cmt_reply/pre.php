<?php
/*
** 活动结束页（未出中奖名单）
*/
require_once(dirname(realpath(__FILE__)).'/init_page.php');
$tplObj->out['list'] = get_ap_list($pre_page_id);
$tplObj->display('lottery/cmt_reply/pre.html');
exit;