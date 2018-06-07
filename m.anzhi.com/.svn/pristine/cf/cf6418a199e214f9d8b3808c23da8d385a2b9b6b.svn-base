<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');

$model = new GoModel();

// 奖品配置
$award_level_option = array(
    'where' => array(
        'config_type' => 'SPRING_LOTTERY_AWARD',
        'status' => 1
    ),
    'cache_time' => 86400,
    'table' => 'pu_config'
);
$award_level_result = $model -> findOne($award_level_option);
$award_config = json_decode($award_level_result['configcontent'],true);

// 最终所有获奖者列表
$all_award_option = array(
	'where' => array(
		'status' => 1
	),
    'field' => 'award, telephone',
	'order' => 'award',
	//'cache_time' => 600,
	'table' => 'spring_lottery_award',
);
$all_award_result = $model -> findAll($all_award_option,'lottery/lottery');
$list = array();
// 根据配置的奖品初始化list
foreach ($award_config as $key => $config) {
    $list[$key+1] = array();
}

foreach ($all_award_result as $record) {
    $award_level = $record['award'];
    $list[$award_level]['award_name'] = $award_config[$award_level-1][0] . ' '. $award_config[$award_level-1][1];
    $list[$award_level]['telephone'][] = substr_replace($record['telephone'],'****',3,4);
}

$tplObj->out['list'] = $list;

$tplObj -> display('spring_lottery_end.html');