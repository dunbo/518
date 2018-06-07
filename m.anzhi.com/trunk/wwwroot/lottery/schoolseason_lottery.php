<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
session_begin();
$aid = $_GET['aid'];
if($_GET['tab']){
	$tab = $_GET['tab'];
}else{
	$tab = rand(1,5);
}

$imsi = $_SESSION['USER_IMSI'];
$version_code = $_SESSION['VERSION_CODE'];
$imsi_num = "schoolseason_lottery:num_{$imsi}_{$aid}";
$imsi_info = "schoolseason_lottery:info_{$imsi}_{$aid}";

//最近中奖信息
$all_award_option = array(
	'where' => array(
		'status' => 1,
		'award_level' => array('exp',' <= 4'),
	),
	'order' => 'create_tm desc',
	'limit' => 10,
	'table' => 'schoolseason_lottery_award',
);
$all_award_result = $model -> findAll($all_award_option,'lottery/lottery');

$award_info_option = array(
	'where' => array(
		'config_type' => 'SCHOOLSEASON_AWARD',
		'status' => 1
	),
	'cache_time' => 86402,
	'table' => 'pu_config'
);
$award_info_result = $model -> findOne($award_info_option);
$award_content = json_decode($award_info_result['configcontent'],true);
if($all_award_result){
	foreach($all_award_result as $key => $val){
		$val['award'] = $award_content[$val['award_level']][1];
		$val['telephone'] = substr_replace($val['telephone'],'****',3,4);
		$all_award_result[$key] = $val;
	}
	$tplObj -> out['all_award_result'] = $all_award_result;
	$tplObj -> out['all_award_count'] = count($all_award_result);
}

$my_info = $redis -> gethash($imsi_info);
if($my_info['status']){
	$award_level = $award_content[$my_info[1]][0];
	$award_prize = $award_content[$my_info[1]][1];
	$tplObj -> out['lottery_status'] = 200;
	$tplObj -> out['award_level'] = $award_level;
	$tplObj -> out['award_prize'] = $award_prize;
}else{
	$award_option = array(
		'where' => array(
			'imsi' => $imsi,
			'status' => 0,
			'award_level' => array('exp','<=4')
		),
		'table' => 'schoolseason_lottery_award'
	);
	$award_result = $model -> findOne($award_option,'lottery/lottery');
	if($award_result){
		$my_award = array($award_result['imsi'],$award_result['award_level'],$award_result['create_tm'],$award_status['status']);
		$redis -> sethash($imsi_info,$my_award);
		$award_level = $award_content[$my_award[1]][0];
		$award_prize = $award_content[$my_award[1]][1];
		$tplObj -> out['award_level'] = $award_level;
		$tplObj -> out['award_prize'] = $award_prize;
		$tplObj -> out['lottery_status'] = 200;
	}
}

$my_num = $redis -> setx('incr',$imsi_num,0);

//根据分类获取该分类下所有软件
$soft_category_option = array(
	'where' => array(
		'active_id' => 712,
		'rank' => $tab,
		'status' => 1
	),
	'table' => 'sj_actives_category'
);
$soft_category_result = $model -> findOne($soft_category_option);
$all_soft = array(
	'where' => array(
		'category_id' => $soft_category_result['id'],
		'page_id' => 712,
		'status' => 1
	),
	'field' => 'package',
	'table' => 'sj_actives_soft'
);
$soft_result = $model -> findAll($all_soft);
foreach($soft_result as $k => $v){
	$the_package[] = $v['package'];
}

$result_soft = gomarket_action('soft.GoGetSoftDetailPackage', array(
	'PACKAGE_NAME' => 'com.gumichina.wcat.anzhi',
	'VR' => 3,
));

$bm_sizes = formatFileSize(1,$result_soft['SOFT_SIZE']);
$tplObj -> out['the_package'] = json_encode($the_package);
$tplObj -> out['bm_sizes'] = $bm_sizes;
$tplObj -> out['result_soft'] = $result_soft;
$tplObj -> out['my_num'] = $my_num;
$tplObj -> out['version_code'] = $version_code;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['aid'] = $aid;
$tplObj -> out['tab'] = $tab;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('schoolseason_lottery.html');










