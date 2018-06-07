<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
$xy_channel = array(
2727 => 1,2728 => 1,2729 => 1,2730 => 1,2731 => 1,2732 => 1,2733 => 1,2734 => 1,2735 => 1,2736 => 1,2737 => 1,2738 => 1,2739 => 1,2740 => 1,2741 => 1,2742 => 1,2743 => 1,2744 => 1,2745 => 1,2746 => 1,2747 => 1,2748 => 1,2749 => 1,3020 => 1,3021 => 1,3022 => 1,3023 => 1,3173 => 1,3174 => 1,3175 => 1,3176 => 1,3177 => 1,3150 => 1,3172 => 1
);
$cid = isset($_GET['cid']) ? $_GET['cid'] : 0;
$pid = isset($_GET['pid']) ? $_GET['pid'] : 1;

$model = new GoModel();
$question_option = array(
	'where' => array(
		'status' => 1,
		'pid' => $pid,
	),
	'order' => 'rank',
	'table' => 'sj_feedback_question'
);

if (!isset($xy_channel[$cid])) {
	$question_option['where']['id'] = array('exp', '<>19');
}

$question_result = $model -> findAll($question_option);

$statement_qq_option = array(
	'where' => array(
		'status' => 1,
		'type' => 1,
		'start_tm' => array('exp','<='.time().' and end_tm >= '.time().''),
	),
	'table' => 'sj_service_statement'
);
$statement_qq_result = $model -> findOne($statement_qq_option);

$statement_tel_option = array(
	'where' => array(
		'status' => 1,
		'type' => 2,
		'start_tm' => array('exp','<='.time().' and end_tm >= '.time().''),
	),
	'table' => 'sj_service_statement'
);
$statement_tel_result = $model -> findOne($statement_tel_option);


$tplObj -> out['http_url'] = HTTP_URL;
$tplObj -> out['pid'] = $pid;
$tplObj -> out['statement_qq_result'] = $statement_qq_result;
$tplObj -> out['statement_tel_result'] = $statement_tel_result;
$tplObj -> out['question_result'] = $question_result;
$tplObj -> out['question_results'] = json_encode($question_result);

if (isset($_GET['softid'])) {
	$tplObj -> out['default'] = 22;
	$tplObj -> out['softid'] = $_GET['softid'];
}

if ($_GET['pid'] == 20) {
	$tplObj -> display('feedback_smzdw.html');
}

if (isset($_GET['tpl_ver']) && $_GET['tpl_ver'] == 'v6') {
	$tplObj -> display('feedback_v6.html');
} else {
	$tplObj -> display('feedback.html');
}