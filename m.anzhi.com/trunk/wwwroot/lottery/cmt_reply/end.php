<?php
/*
** 活动结束页（已出中奖名单）
*/
require_once(dirname(realpath(__FILE__)).'/init_page.php');
$list = get_ap_list($end_page_id);
$list['ap_award'] = str_replace('<p>', '<dd>', str_replace('</p>', '</dd>', $list['ap_award']));
$tplObj->out['list'] = $list;
$tplObj->display('lottery/cmt_reply/end.html');
exit;