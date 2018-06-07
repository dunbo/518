<?php
	
	include_once (dirname(realpath(__FILE__)).'/init.php');
	$model = new GoModel();

	$guess_result = array(array('mobile' => '18946106602','award_level' => 3,'award_time' => '1405211764'),array('mobile' => '18024620615','award_level' => 3,'award_time' => '1405211764'),array('mobile' => '15879474060','award_level' => 3,'award_time' => '1405211764'),array('mobile' => '18291371533','award_level' => 3,'award_time' => '1405211764'),array('mobile' => '13210698622','award_level' => 4,'award_time' => '1405211764'),array('mobile' => '13475270068','award_level' => 4,'award_time' => '1405211764'),array('mobile' => '18975231777','award_level' => 4,'award_time' => '1404955015'),array('mobile' => '18686060398','award_level' => 3,'award_time' => '1404955015'),array('mobile' => '18026377171','award_level' => 3,'award_time' => '1404955015'),array('mobile' => '18356640763','award_level' => 3,'award_time' => '1404868739'));
	$award_option = array(
		'where' => array(
			'config_type' => 'WORLD_CUP_LEVEL',
			'status' => 1
		),
		'cache_time' => 600,
		'table' => 'pu_config'
	);
	$award_result = $model -> findOne($award_option);
	$award_level = json_decode($award_result['configcontent'],true);
	
	foreach($guess_result as $key => $val){
		$val['award_money'] = $award_level[$val['award_level']];
		$val['the_time'] = date('Y-m-d',$val['award_time']);
		$val['telphone'] = substr_replace($val['mobile'],'****',3,4);
		$guess_result[$key] = $val;
	}
	
	$tplObj -> out['guess_result'] = $guess_result;
	$tplObj -> display('worldcup_end.html');