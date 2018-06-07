<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis','lottery');

$build_query = http_build_query($_GET);
if($configs['is_test'] == 1){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$configs['activity_url']."lottery/appointment/index.php?".$build_query;
//$login_url = $center_url."http://activity.test.anzhi.com/lottery/appointment/end.php?".$build_query;//6.4切换完替换回来
$tplObj -> out['login_url'] = $login_url;
$aid = $active_id = $_GET['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}

$sid = $_GET['sid'];
$model = new GoModel();
session_begin();
$time = time();

if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

user_loging_new();
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
		//登录日志
		$log_data = array(
			'uid' => $_SESSION['USER_UID'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $sid,
			'time' => $time,
			'key' => 'login'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		$uid = $_SESSION['USER_UID'];

		$tplObj -> out['username'] = $_SESSION['USER_NAME'];
		$tplObj -> out['is_login'] = 1;
		$tplObj -> out['uid'] = $uid;
}else{//未登录
	$tplObj -> out['is_login'] = 2;
}


//$message = session_begin();
//$imsi = $_SESSION['USER_IMSI'];
//$imsi = $_SESSION['USER_UID'];
$model = new GoModel();
$activity_option = array(
	'where' => array(
		'id' => $aid
	),
	'cache_time' => 3600,
	'table' => 'sj_activity'
);
$activity_result = $model -> findOne($activity_option);
$page_option = array(
	'where' => array(
		'ap_id' => $activity_result['activity_end_id']
	),
	'cache_time' => 3600,
	'table' => 'sj_activity_page'
);
$page_result = $model -> findOne($page_option);


$page_option2 = array(
	'where' => array(
		'ap_id' => $activity_result['activity_page_id']
	),
	'cache_time' => 3600,
	'table' => 'sj_activity_page'
);
$page_result2 = $model -> findOne($page_option2);

/*
if($page_result['show_award'] == 1){
	$award_option = array(
		'where' => array(
			'aid' => $aid
		),
		'cache_time' => 3600,
		'table' => 'gm_lottery_award'
	);
	$award_result = $model -> findAll($award_option,'lottery/lottery');
	$this -> assign('award_result',$award_result);
}
*/

$version_code = $_SESSION['VERSION_CODE'];
$tplObj -> out['version_code'] = $version_code;
$tplObj -> out['imgurl'] = getImageHost();
$tplObj -> out['aid'] = $aid;
$tplObj -> out['sid'] = $sid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['activity_result'] = $activity_result;
$tplObj -> out['page_result'] = $page_result;
$tplObj -> out['page_result2'] = $page_result2;
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['ap_desc'] = htmlspecialchars_decode($page_result['ap_desc']);
$tplObj -> out['ap_rule'] = htmlspecialchars_decode($page_result['ap_rule']);
$tplObj -> display('lottery/appointment/end.html');
