<?php
include_once (dirname(realpath(__FILE__)).'/init.php');
$model = new GoModel();
$id = $_GET['id'];
$opt = array(
	'table' => 'sj_activity_page',
	'where' =>array(
		'ap_id' =>$id
	),
);
$result['ap_rule'] = nl2br($result['ap_rule']);
$result['ap_notice'] = nl2br($result['ap_notice']);
$tplObj -> out['imgurl'] = getImageHost();
$result = $model->findOne($opt);
$tplObj->out['result'] = $result;
$tplObj->display("adactivity.html");

