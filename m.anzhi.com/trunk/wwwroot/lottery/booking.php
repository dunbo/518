<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$ap_id = $_GET['ap_id'] ? $_GET['ap_id'] : $_POST['ap_id'];
$time = time();

session_begin($sid);
user_loging_reserve();	
$sid = session_id();
// 常量
if ($_SERVER['SERVER_ADDR'] == '118.26.203.23') {
	define('SHARE_PROMOTION_HOST', 'http://m.test.anzhi.com');
	//define('SHARE_M_HOST', 'http://m.test.anzhi.com');
} else {
	define('SHARE_PROMOTION_HOST', 'http://promotion.anzhi.com');
	//define('SHARE_M_HOST', 'http://m.anzhi.com');
	//define('SHARE_FX_HOST', 'http://fx.anzhi.com');
}

if($_POST){
	if($_POST['type']==1)
	{
		$log_data = array(
			'uid'=> $_SESSION['USER_UID']?$_SESSION['USER_UID']:'',
			'username' => $_SESSION['USER_NAME']?$_SESSION['USER_NAME']:'',
			'package' => $_POST['package'],
			'time' => $time,
			'sid'=>$sid,
			'activity_id' => $active_id,
			'ap_id'=> $ap_id,		
			'key' => 'download_soft'
		);
	}
	else
	{
		$log_data = array(
			'uid'=> $_SESSION['USER_UID']?$_SESSION['USER_UID']:'',
			'username' => $_SESSION['USER_NAME']?$_SESSION['USER_NAME']:'',
			'phone' => $_POST['mobile_phone'],
			'time' => $time,
			'sid'=>$sid,
			'activity_id' => $active_id,
			'ap_id'=> $ap_id,		
			'key' => 'booking'
		);
	}
	permanentlog('activity_page_'.$ap_id.'.log', json_encode($log_data));	
	exit(json_encode(array('code'=>1,'msg'=>'成功')));	
}else{
	if($_GET['last_page'] == 1){
		//最后一页日志
		$log_data = array(
			"imsi" => $_SESSION['USER_IMSI'],
			"device_id" => $_SESSION['DEVICEID'],
			"activity_id" => $active_id,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"sid" => $sid,
			"time" => $time,
			"user" => $_SESSION['USER_NAME']?$_SESSION['USER_NAME']:'',
			'uid'=> $_SESSION['USER_UID']?$_SESSION['USER_UID']:'',
			'ap_id'=> $ap_id,
			'key' => 'last_page'
		);
		permanentlog('activity_page_'.$ap_id.'.log', json_encode($log_data));	
		exit(json_encode(array('code'=>1,'msg'=>'成功')));		
	}
	//日志
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME']?$_SESSION['USER_NAME']:'',
		'uid'=> $_SESSION['USER_UID']?$_SESSION['USER_UID']:'',
		'ap_id'=> $ap_id,
		'key' => 'show_homepage'
	);
	permanentlog('activity_page_'.$ap_id.'.log', json_encode($log_data));	
	
	$option = array(
		'where' => array(
			'ap_id' => $ap_id,
		),
		'table' => 'sj_activity_page',
		'cache_time' => 30*60
	);
	$list = $model->findOne($option);	
	
	//获取跳转的id
	$jump_option = array(
		'where' => array(
			'page_type' => 'otherfixed_homepage_recommend',//首页推荐的key
			'status' =>1,
			'content_type'=>4,	
		),
		'table' => 'sj_common_jump',
		'order' => 'create_at DESC',
		'cache_time' => 30*60
	);
	$jump_result = $model->findOne($jump_option);
	
	//查找最新的版本号
	$soft_option = array(
		'where' => array(
			'package' => 'cn.goapk.market',//安智市场包
			'status' =>1,
			'hide'=>1,	
		),
		'table' => 'sj_soft',
		'order' => 'version_code DESC',
		'cache_time' => 30*60
	);
	$soft_result = $model->findOne($soft_option);
	
	// 获得微信授权
	include(dirname(realpath(__FILE__)).'/public/WeixinShareAuth.class.php');
	$wx_share_auth = new WeixinShareAuth();
	$wx_share_config = $wx_share_auth->get_config();
	$tplObj->out['wx_share_config'] = json_encode($wx_share_config);

	//var_dump($list);
	$tplObj -> out['jump_id'] = $jump_result['id'];
	$tplObj -> out['soft_version'] = $soft_result['version_code'];
	$tplObj -> out['list'] = $list;
	$tplObj -> out['aid'] = $active_id;
	$tplObj -> out['sid'] = $sid;
	$tplObj -> out['ap_id'] = $ap_id;
	$tplObj -> out['img_url'] = getImageHost();	
	$tplObj -> out['share_url'] = SHARE_PROMOTION_HOST . '/lottery/booking.php?ap_id='.$ap_id;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> out['new_static_url'] = $configs['new_static_url'];
	$tplObj -> display('lottery/booking.html');
}