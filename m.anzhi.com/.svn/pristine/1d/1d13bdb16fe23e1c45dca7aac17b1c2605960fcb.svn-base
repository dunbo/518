<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$active_id = 179;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
if($_GET['sid'] && eregi('[0-9a-zA-z]', $_GET['sid']) && strlen($_GET['sid']) == 32){
	session_id($_GET['sid']);
}
$aid=$_GET['aid'];
$key_actsid=get_key();
if($key_actsid)
{
	$info= $redis -> get($key_actsid);
	$info['get_share']=1;
	$info['url']="http://118.26.203.23/lottery/christmas_lottery_end.php";
	$save_info=$redis -> set($key_actsid,$info);
}
session_start();
$imsi = $_SESSION['USER_IMSI'];
$cid = $_SESSION['MODEL_CID'];
$alone_update = $_SESSION['alone_update'];
if($alone_update == 1 && $_SESSION['VERSION_CODE'] < 5410){
	$channel_option = array(
		'where' => array(
			'cid' => $cid,
			'status' => 1,
			'version_code' => array('exp','>=5400'),
			'limit_rules' => array('exp'," ='' or limit_rules is null "),
		),
		'cache_time' => 3600,
		'table' => 'sj_market',
	);
	$channel_result = $model -> findAll($channel_option);
	if(!$channel_result){
		$channel_status = 100;
	}else{
		$channel_status = 200;	
	}
	$tplObj -> out['channel_status'] = $channel_status;
}

if(!$imsi || $imsi == '000000000000000'){
	$tplObj -> out['status'] = 100;
}

if($imsi){
	if($_SESSION['alone_update']==0 && $_SESSION['VERSION_CODE'] < 5410){
		$version_status = 200;
		$intro_option = array(
			'where' => array(
				'package' => 'cn.goapk.market'
			),
			'field' => 'softid,softname,version_code',
			'order' => 'softid DESC',
			'limit' => 1,
			'cache_time' => 86400,
			'table' => 'sj_soft'
		);
		$intro_result = $model -> findOne($intro_option);

		$intro_size_option = array(
			'where' => array(
				'softid' => $intro_result['softid']
			),
			'field' => 'filesize',
			'table' => 'sj_soft_file',
			'cache_time' => 86400
		);
		$intro_size_result = $model -> findOne($intro_size_option);
		$intro_result['soft_sizes'] = formatFileSize('',$intro_size_result['filesize']);
		$intro_result['soft_size'] = $intro_size_result['filesize'];
	}else{
		$version_status = 1000;
	}
	$tplObj -> out['version_status'] = $version_status;
	$tplObj -> out['intro_result'] = $intro_result;
}

$imsi_redis = $imsi.":lottery_{$active_id}";
$imsi_num = $imsi.":lottery_num_{$active_id}";
$imsi_info = $imsi.":info_{$active_id}";

//最近中奖信息
$all_award_option = array(
	'where' => array(
		'status' => 1
	),
	'order' => 'time desc',
	'limit' => 10,
	'cache_time' => 600,
	'table' => 'christmas_award',
);
$all_award_result = $model -> findAll($all_award_option,'lottery/lottery');

if($all_award_result){
	$award_config_option = array(
		'where' => array(
			'config_type' => 'CHRISTMAS_AWARD',
			'status' => 1
		),
		'cache_time' => 86400,
		'table' => 'pu_config'
	);
	$award_config_result = $model -> findOne($award_config_option);
	$award_level = json_decode($award_config_result['configcontent'],true);
	foreach($all_award_result as $key => $val){
		$val['award'] = $award_level[$val['award']-1][1].'一张';
		$val['the_time'] = date('Y-m-d',$val['time']);
		$val['telphone'] = substr_replace($val['telphone'],'****',3,4);
		$all_award_result[$key] = $val;
	}
}

//进入活动按钮状态
$imsi_share = $imsi.":share_time_{$active_id}";
$today_time = strtotime(date('Ymd 00:00::00',time()));
$before_share = $redis -> gethash($imsi_share);
$my_num = $redis -> setx('incr',$imsi_num,0);
if($my_num || $before_share['time'] > $today_time){
	$share_status = 100;
}else{
	$share_status = 200;
}
$one_time = strtotime(date('20141223 00:00:00'));
$two_time = strtotime(date('20141225 00:00:00'));
$three_time = strtotime(date('20141225 23:59:59'));
$now_time = time();
$share = $_GET['share'];
$tplObj -> out['one_time'] = $one_time;
$tplObj -> out['now_time'] = $now_time;
$tplObj -> out['two_time'] = $two_time;
$tplObj -> out['three_time'] = $three_time;
$tplObj -> out['share'] = $share;
$tplObj -> out['img_url'] = getImageHost();
$tplObj -> out['share_status'] = $share_status;
$tplObj -> out['all_award_result'] = $all_award_result;
$tplObj -> out['all_award_count'] = count($all_award_result);
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['aid'] = $_GET['aid'];
$tplObj -> out['actsid'] = $key_actsid;
$tplObj -> display('christmas_share.html');