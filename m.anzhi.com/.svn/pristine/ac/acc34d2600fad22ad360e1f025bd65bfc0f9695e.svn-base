<?php

require_once(dirname(realpath(__FILE__)) . '/weixin_init.php');

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'actsid' => $actsid,
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'key' => 'weixin_index'
);
permanentlog($activity_log_file, json_encode($log_data));

$score = $redis->gethash($actsid, 'score');
if (empty($score)) {
	$score = 0;
}
$tplObj->out['score'] = $score;

$tplObj->display("lottery/guessappbattle/weixin_index.html");