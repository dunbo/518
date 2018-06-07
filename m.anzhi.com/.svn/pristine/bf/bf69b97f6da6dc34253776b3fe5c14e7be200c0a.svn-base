<?php

/*
** 光阴的信
*/

require_once(dirname(realpath(__FILE__)) . '/timememory_init_page.php');

// 如果有post数据过来，写缓存
if ($_GET['sex']) {
	// 检查一下数据，准备写缓存
	$sex = trim($_GET['sex']);
	$teacher = trim($_GET['teacher']);
	$grade = trim($_GET['grade']);
	$gossip = trim($_GET['gossip']);
	$fan = trim($_GET['fan']);
	$love = trim($_GET['love']);
	
	$sex_ret = preg_match('/span_sex_(\d)/', $sex, $sex_match);
	$teacher_ret = preg_match('/span_teacher_(\d)/', $teacher, $teacher_match);
	$grade_ret = preg_match('/span_grade_(\d)/', $grade, $grade_match);
	$gossip_ret = preg_match('/span_gossip_(\d)/', $gossip, $gossip_match);
	$fan_ret = preg_match('/span_fan_(\d)/', $fan, $fan_match);
	$love_ret = preg_match('/span_love_(\d)/', $love, $love_match);
	
	if (!$sex_ret || !$teacher_ret || !$grade_ret || !$gossip_ret || !$fan_ret || !$love_ret) {
		exit;
	}
	$sex_value = $sex_match[1];
	$teacher_value = $teacher_match[1];
	$grade_value = $grade_match[1];
	$gossip_value = $gossip_match[1];
	$fan_value = $fan_match[1];
	$love_value = $love_match[1];
	
	// 写缓存
	$redis->set($rkey_imsi_question_answer, "{$sex_value},{$teacher_value},{$grade_value},{$gossip_value},{$fan_value},{$love_value}", $r_cache_time);
}

// 从redis中读出此用户填写过的答案
$question_answer = $redis->get($rkey_imsi_question_answer);
$answer_arr = explode(',', $question_answer);

// 从redis中判断此用户今天是否已经分享过
$last_share_day = $redis->get($rkey_imsi_share_info);
$has_shared = $last_share_day == $today ? 1 : 0;

$tplObj->out['answer_arr'] = $answer_arr;
$tplObj->out['has_shared'] = $has_shared;
$tplObj->display('timememory_letter.html');
exit;