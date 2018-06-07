<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$time = time();
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
//ctype_digit  检查时候是只包含数字字符的字符串（0-9）
if(!ctype_digit($active_id)){
	echo '活动id无效';
	exit;
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$stop = $_GET['stop'] ? $_GET['stop'] : $_POST['stop'];
session_begin($sid);	
$prefix = 'vip_discount';
if($stop != 1){
	$res = activity_is_stop($active_id);
	if(!$res){
		$url = $activity_host."/lottery/vip/vip_discount.php?stop=1&aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
	}
}
$tplObj -> out['prefix'] = $prefix;
if($_GET['click']){
	$log_data = array(
		"activity_id" => $active_id,
		"chl_cid" => $_SESSION['CHL_CID'] ? $_SESSION['CHL_CID'] : '',
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		'product' => $_SESSION['product'],//1市场 13 sdk
		"click" => $_GET['click'],//1开通会员2会员权益页
		'key' => 'go_jump',
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	if($_GET['click'] == 2){
		$config_data = get_config();
		$tplObj -> out['img_url'] =  getImageHost();
		$config_data['lost_no_desc'] = nl2br($config_data['lost_no_desc']);
		$tplObj -> out['list'] = $config_data;		
		$tplObj -> out['new_static_url'] = $configs['new_static_url'];
		$tpl = "lottery/vip/discount_detail.html";	
		$tplObj -> display($tpl);
	}
	exit;
}
//日志
$log_data = array(
	"imsi"	=>	$_SESSION['USER_IMSI'],
	"device_id"		=>	$_SESSION['DEVICEID'],
	"chl_cid" => $_SESSION['CHL_CID'] ? $_SESSION['CHL_CID'] : '',
	"mac" 	=>  $_SESSION['MAC'],
	"ip"	=>	$_SERVER['REMOTE_ADDR'],
	"sid"	=>	$sid,
	"time"	=>	$time,
	'product' => $_SESSION['product'],//1市场 13 sdk
	"user"	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
	'uid'	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
	'key'	=>	'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

if(is_weixin() || $_GET['is_weixin']){
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tpl = "lottery/vip/weixin.html";	
	$tplObj -> display($tpl);	
	exit;
}

$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['is_sdk'] = $_SESSION['product'] == 13 ? 1 : 0;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['activity_host'] = $configs['activity_url'];
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['soft_info']  =  $soft_info;
$tplObj -> out['is_test'] = $configs['is_test'];
$config_data = get_config($stop);
$tplObj -> out['img_url'] =  getImageHost();
if($stop == 1){
	$config_data['change_button'] = nl2br($config_data['change_button']);
	$tpl = "lottery/vip/discount_end.html";	
}else{
	$config_data['ap_rule'] = nl2br($config_data['ap_rule']);
	$config_data['no_marquee'] = nl2br($config_data['no_marquee']);
	$tpl = "lottery/vip/discount_index.html";	
}
$tplObj -> out['list'] = $config_data;
$tplObj -> display($tpl);


//获取配置
function get_config($stop){
	global $prefix,$active_id,$model,$redis,$configs;
	$config_key = "{$prefix}:{$active_id}:config:".$stop;
	$list = $redis->get($config_key);	
	if($list === null){	
		$option = array(
			'where' => array(
				'id' => $active_id,
			),
			'table' => 'sj_activity',
			'field' => 'name,activity_page_id,activity_end_id,start_tm,end_tm,channel_id',
		);
		$activity = $model->findOne($option);	
		if($stop == 1){
			$activity_page_id = $activity['activity_end_id'];//结束pageid
		}else{
			$activity_page_id = $activity['activity_page_id'];
		}
		//活动天数
		$act_day = intval(($activity['end_tm']-$activity['start_tm'])/86400);	
		$option = array(
			'where' => array(
				'ap_id' => $activity_page_id,
			),
			'table' => 'sj_activity_page',
		);
		$list = $model->findOne($option);	
		$list['acrivity_name'] = $activity['name'];
		$list['start_tm'] = $activity['start_tm'];
		$list['end_tm'] = $activity['end_tm'];
		$list['channel_id'] = $activity['channel_id'];
		$list['acrivity_day'] = $act_day;	
		if($configs['is_test'] == 1 ) {
			$redis->set($config_key,$list, 60*5);
		}else{
			$redis->set($config_key,$list, 60*30);
		}
	}
	return $list;			
}