<?php
include_once ('./fun.php');	
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj -> out['activity_host'] = $configs['activity_url'];
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;
if($configs['is_test'] == 1 ) {
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
if(is_weixin() || $_GET['is_weixin']){
	$tpl = "lottery/vip/weixin.html";	
	$tplObj -> display($tpl);	
	exit;
}
$is_new_user = deviceid_activation_state();
$tplObj -> out['is_new_user'] = $is_new_user;
if($from_type == 2 && $_GET['is_coll'] == 1){
	//合集页面
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $_SESSION['USER_UID'],
		'key' => 'pageindex'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	
	$tplObj -> out['is_day25'] = ($time-strtotime("2017-12-25")) >= 0 ? 1 : 0;		
	$tpl = "lottery/".$prefix."/collection.html";	
	$tplObj -> display($tpl);	
	exit;	
}
if($_POST['is_log'] == 1){
	//点击跳转
	$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $_SESSION['USER_UID'],
		'type'=> $_POST['type'],//1流量大波送 2大奖抽抽抽 3 砸蛋送惊喜 4拆红包
		'key' => 'go_url'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
	exit;
}
if($_POST['flow_extract'] == 1){
	$bind_list = get_bind_status();	
	$mobile = $bind_list['mobile'];
	$price = $_POST['price'];
	if(!$mobile){
		exit(json_encode(array('code'=>0,'msg'=>'请先绑定手机号')));
	}	
	$ret = flow_recharge($mobile,$price,20000);
	exit(json_encode($ret));
}
if($_POST['send_code'] == 1){
	$mobile = $_POST['mobile_phone'];
	$ret = get_phone_ascription($mobile);		
	if($ret['ownoperator'] == '101'){
		exit(json_encode(array('code'=>0,'msg'=>'很抱歉，活动目前仅支持联通、移动、电信三大运营商流量包兑换~')));
	}	
	$res = send_active_mobile($mobile);
	exit(json_encode($res));
}else if($_POST['binding'] == 1){
	$code = $_POST['code'];
	$mobile = $_POST['mobile_phone'];
	$res = check_mobile($mobile,$code);
	exit(json_encode($res));
}else if($_POST['sign_flow'] == 1 || $_POST['sign_flow'] == 2){
	//下载领取流量
	if($active_id == 1242){
		$price = 50;
	}else{
		$price = 30;
	}
	$res = collar_down_flow($price,3);
	exit(json_encode($res));
}else if($_POST['down'] == 1){
	//下载获取流量
	$res = get_down_flow(3);
	exit(json_encode($res));	
}
//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'],
		'uid'=> $_SESSION['USER_UID'],
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
$tplObj -> out['static_url'] = $configs['static_url'];
if($_SESSION['VERSION_CODE'] < 6000 || $imsi == ''){
	$soft_model = load_model('softlist');
	$anzhilist = $soft_model->getPackageToSoftId("cn.goapk.market");
	//$anzhiid = $anzhilist[0];
	$anzhiid = array_pop($anzhilist);
	$soft_info = $soft_model ->getsoftinfos($anzhiid, getFilterOption());
	$tplObj -> out['alone_update'] = $alone_update;
	$tplObj -> out['soft_info'] = $soft_info[$anzhiid];
	$tpl = "lottery/".$prefix."/update".$from_type.".html";	
}else if($stop == 1){
	$tpl = "lottery/".$prefix."/end".$from_type.".html";	
}else{
	$grant_flow = grant_flow();
	$my_prize = my_prize();	
	$tplObj -> out['log_price_total'] = $my_prize['price_total'];
	$tplObj -> out['res_flow'] = $my_prize['price_total']-$grant_flow['price_total'];
	$tpl = "lottery/".$prefix."/index3.html";	
}

$tplObj -> out['bind_status'] = get_bind_status();
$tplObj -> out['imsi'] = $imsi;	
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$sign_flow_key = $prefix.":".$active_id.":sign_flow:".$imsi.":".$today;
$down_flow_key = $prefix.":".$active_id.":down_flow:".$imsi.":".$today;

$tplObj -> out['sign_flow'] = $redis->get($sign_flow_key);
$tplObj -> out['down_flow'] = $redis->get($down_flow_key) ? $redis->get($down_flow_key) :0;
$down_num_key = $prefix.":".$active_id.":down_num:".$imsi.":".$today;
$tplObj -> out['down_num'] = $redis->get($down_num_key) ? $redis->get($down_num_key) : 0;
$tplObj -> display($tpl);