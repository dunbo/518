<?php

include_once (dirname(realpath(__FILE__)).'/weixin_init.php');

if (empty($actsid)) {
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
	$redis->sethash($actsid, array('score' => $score), $r_cache_time);
} else {
	$score = $redis->gethash($actsid, 'score');
}

// 跳转地址
$redis->sethash($actsid, array('url' => SHARE_PROMOTION_HOST . '/lottery/guessappbattle/lottery.php'));

echo $score;
exit;