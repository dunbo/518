<?php
/*
** 活动首页
*/
require_once(dirname(realpath(__FILE__)).'/init_page.php');

// 判断活动是否已结束
if (time() > $a_end_time) {
	// 跳转到活动结束页
	header("location:{$a_end_url}");
	exit;
}

// 判断活动是否已开始
if (time() < $a_start_time) {
    // 跳转到活动结束页
    header("location:{$a_pre_url}");
    exit;
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $sid,
    'time' => time(),
    'key' => 'show_homepage'
);
permanentlog($activity_log_file, json_encode($log_data));
$list = get_ap_list($page_id);
$list['award_color'] = explode(',', $list['award_color']);
$list['ap_desc'] = str_replace('<p>', '<li>', str_replace('</p>', '</li>', $list['ap_desc']));
$tplObj->out['list'] = $list;
$tplObj->display('lottery/cmt_reply/index.html');
exit;