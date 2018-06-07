<?php

include_once (dirname(realpath(__FILE__)).'/init.php');

if (!$imsi_status) {
	exit(0);
}

// 答题分数
if (isset($_POST['correct_number'])) {
	$correct_number = $_POST['correct_number'];
	$correct_number = (int)$correct_number;
	if ($correct_number < 0) {
		$correct_number = 0;
	} else if ($correct_number > 8) {
		$correct_number = 8;
	}
	$score = $correctnumber_score_arr[$correct_number];
	$redis->set($rkey_imsi_score, $score, $r_cache_time);
} else {
	$score = $redis->get($rkey_imsi_score);
}

$tplObj->out['score'] = $score ? $score : 0;

echo $score;
exit;