<?php

require_once(dirname(realpath(__FILE__)) . '/timememory_init_page.php');

if ($_GET['manual'] != 1) {
	// 判断用户是否填写过回忆答案
	$question_answer = $redis->get($rkey_imsi_question_answer);

	if (!empty($question_answer)) {
		// 填写过答案，直接跳转到光阴的信
		header('location:/lottery/timememory_letter.php?sid='.$_GET['sid']);
		exit;
	}
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $aid,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
	'users' => '',
    'uid' => '',
    'key' => 'show_homepage'
);
permanentlog($activity_log_file, json_encode($log_data));

// 手工返回此页，或未填写过答案，显示问题页
$tplObj->display('timememory_index.html');
exit;