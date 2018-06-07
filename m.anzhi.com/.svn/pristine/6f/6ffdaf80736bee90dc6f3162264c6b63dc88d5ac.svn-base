<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$model = new GoModel();
$award = array(1,2,3,4,5,6);

foreach($award as $key => $val){
	$option = array(
		'where' => array(
			'status' => 1,
			'award' => $val
		),
		'cache_time' => 3605,
		'table' => 'nd_award'
	);
	$result = $model -> findAll($option,'lottery/lottery');
	foreach($result as $k => $v){
		$v['telphones'] = substr_replace($v['telphone'],'****',3,4);
		$result[$k] = $v;
	}
	$level_option = array(
		'where' => array(
			'config_type' => 'ND_AWARD',
			'status' => 1
		),
		'cache_time' => 3605,
		'field' => 'configcontent',
		'table' => 'pu_config'
	);
	$level_result = $model -> findOne($level_option);
	$level_info = json_decode($level_result['configcontent'],true);
	$this_level = $level_info[$val][0];
	$all_result[$key]['level'] = $this_level;
	$all_result[$key]['list'] = $result;
}

$tplObj -> out['all_result'] = $all_result;
$tplObj -> display('lottery_list.html');