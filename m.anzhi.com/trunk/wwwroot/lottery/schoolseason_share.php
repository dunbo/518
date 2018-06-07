<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$share_key = $_GET['share_key'];
$sid = $_GET['sid'];
$aid = $_GET['aid'];
$tab = $_GET['tab'];
session_begin();
$model = new GoModel();
$imsi = $_SESSION['USER_IMSI'];
$imsi_share = "schoolseason_lottery:share_{$imsi}_{$aid}";
$time = date('Ymd');
$my_share = $redis -> gethash($imsi_share,$time);

if($my_share){
	$status = 100;
	$tplObj -> out['status'] = 100;
}else{
	$status = 200;
	$tplObj -> out['status'] = 200;
}

$a = array(1=>array('一等奖','红米手机一台'),2=>array('二等奖','百度影棒一个'),3=>array('三等奖','小米手环一个'),4=>array('四等奖','愤怒的小鸟卡包一个'),5=>array('五等奖','白猫计划礼包'));

$b = '{"1":["\u4e00\u7b49\u5956","\u7ea2\u7c73\u624b\u673a\u4e00\u53f0"],"2":["\u4e8c\u7b49\u5956","\u767e\u5ea6\u5f71\u68d2\u4e00\u4e2a"],"3":["\u4e09\u7b49\u5956","\u5c0f\u7c73\u624b\u73af\u4e00\u4e2a"],"4":["\u56db\u7b49\u5956","\u6124\u6012\u7684\u5c0f\u9e1f\u5361\u5305\u4e00\u4e2a"],"5":["\u4e94\u7b49\u5956","\u767d\u732b\u8ba1\u5212\u793c\u5305"]}';

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

$tplObj -> out['sid'] = $sid;
$tplObj -> out['aid'] = $aid;
$tplObj -> out['tab'] = $tab;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> display('schoolseason_share.html');