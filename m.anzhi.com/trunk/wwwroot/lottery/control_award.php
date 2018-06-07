<?php

ini_set("memory_limit", "256M");
ini_set('display_errors', 1);
define('DS', DIRECTORY_SEPARATOR);
define('GO_APP_ROOT', dirname(realpath(__FILE__)));
define('GO_CONFIG_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'config');
define('GO_HELPER_DIR', GO_APP_ROOT. DS. '..'. DS. 'newgomarket.goapk.com'. DS. 'helper');
include_once GO_APP_ROOT. DS. '..'. DS. 'GoPHP'. DS. 'Startup.php';
include_once GO_APP_ROOT . '/lib/Email.class.php';
$model = new GoModel();
$model = new GoModel();
$table = $argv[1];
$award = $argv[2];
if($award){
	$award_level = $award;
}else{
	$award_level = 999;
}
$all_option = array(
	'where' => array(
		'award_level' => array('exp'," < {$award_level}"),
	),
	'table' => $table
);
$all_result = $model -> findAll($all_option,'lottery/lottery');

//所有中实物奖的数量
$all_result_count = count($all_result);
$need_option = array(
	'where' => array(
		'award_level' => array('exp'," < {$award_level}"),
		'status' => 1,
	),
	'table' => $table,
);

$need_result = $model -> findAll($need_option,'lottery/lottery');

$gift_option = array(
	'where' => array(
		'award_level' => array('exp'," > {$award_level}"),
		'status' => 1,
	),
	'table' => $table,
);

$gift_result = $model -> findAll($gift_option,'lottery/lottery');
//礼包中奖数量
$gift_result_count = count($gift_result);

//所有已填写信息实物奖数量
$need_result_count = count($need_result);
$config['game_info']['from'] = array(
    'Host' => '192.168.1.50',
    'Username' => 'service',
    'Password' => 'azbbs2012',
    'From' => 'service@anzhi.com.cn',
    'FromName' => 'anzhi-caiji',
);

$config['game_info']['toGroup'] = array(
    'yanyang@anzhi.com',
	'linhongqing@anzhi.com'
);
$mail_from = $config['game_info']['from'];
$to_address = $config['game_info']['toGroup'];
$cc_address = '';
$subject = '活动4中奖监控';
$content = "已中奖数量（实物奖）：{$all_result_count} <br /> 已填写数量（实物奖）：{$need_result_count} <br /> 礼包中奖数量：{}";
$ret = Email::sendMail($mail_from, $to_address, $cc_address, $subject, $content);

echo 'end';

