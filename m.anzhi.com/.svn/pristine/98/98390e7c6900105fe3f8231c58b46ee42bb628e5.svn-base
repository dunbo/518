<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
$ap_id = $_GET['id'];
$model = new GoModel();
$opts = array(
	'table' => 'sj_activity_page',
	'where' => array(
		'ap_id' => $ap_id
	),
);
$activity = $model->findOne($opts);
$activity['ap_rule'] = nl2br($activity['ap_rule']);
$activity['ap_notice'] = nl2br($activity['ap_notice']);
$activity['ap_award'] = nl2br($activity['award']);
$activity['winning_comment'] = nl2br($activity['winning_comment']);
$activity['button_comment'] = nl2br($activity['button_comment']);
$activity['download_comment'] = nl2br($activity['download_comment']);
$tplObj -> out['imgurl'] = getImageHost();
if($activity['ap_type']==1){
	$tplObj->out['activity'] = $activity;
	$tplObj->display("activities.html");
}else if($activity['ap_type']==2){
	$tplObj->out['activity'] = $activity;
	$tplObj->display("award.html");
}else if($activity['ap_type']==3){
	$tplObj->out['activity'] = $activity;
	$tplObj->display("preview.html");
}else if($activity['ap_type']==4){
	$tplObj->out['activity'] = $activity;
	$tplObj->display("wait.html");
}