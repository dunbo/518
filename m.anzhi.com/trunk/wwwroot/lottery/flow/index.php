<?php
include_once ('./fun.php');	
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['is_share'] = $_GET['is_share'];
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $sid;

if(is_weixin() || $_GET['is_weixin']){
	$tpl = "lottery/vip/weixin.html";	
	$tplObj -> display($tpl);	
	exit;
}


$activation_state = deviceid_activation_state();
if(!$activation_state && $_GET['DUG'] != 1){
	$tpl = "lottery/".$prefix."/activation_state.html";	
	$tplObj -> display($tpl);	
	exit;
}

$build_query = http_build_query($_GET);
if($configs['is_test'] == 1 ) {
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
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
	//1签到领取流量 2下载领取流量
	$res = collar_flow($_POST['sign_flow']);
	exit(json_encode($res));
}else if($_POST['down'] == 1){
	//下载获取流量
	$res = get_down_flow();
	exit(json_encode($res));	
}else if($_POST['guide_read'] == 1){
	//引导层是否已查看
	//$imsi = '460028011771946';
	$guide_read_key = $prefix.":".$active_id.":guide_read:".$imsi;
	$redis->set($guide_read_key,1,60*86400);	
	exit(json_encode(array('code'=>1)));
} else if($_POST['guide_read2'] == 1){
	//引导层是否已查看
	//$imsi = '460028011771946';
	$guide_read2_key = $prefix.":".$active_id.":guide_read2:".$imsi.":".$today;
	$redis->set($guide_read2_key,1,86400);	
	exit(json_encode(array('code'=>1)));
} 
//日志
$log_data = array(
		"imsi" => $imsi,
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
	$tpl = "lottery/".$prefix."/update.html";	
}else if($stop == 1){
	$tpl = "lottery/".$prefix."/end.html";	
}else{
	$grant_flow = grant_flow();
	$my_prize = my_prize();	
	$tplObj -> out['log_price_total'] = $my_prize['price_total'];
	$tplObj -> out['res_flow'] = $my_prize['price_total']-$grant_flow['price_total'];
	$tpl = "lottery/".$prefix."/index.html";	
}

$tplObj -> out['bind_status'] = get_bind_status();
$tplObj -> out['imsi'] = $imsi;	
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> out['activity_host'] = $configs['activity_url'];
$sign_flow_key = $prefix.":".$active_id.":sign_flow:".$imsi.":".$today;
$down_flow_key = $prefix.":".$active_id.":down_flow:".$imsi.":".$today;
$tplObj -> out['sign_flow'] = $redis->get($sign_flow_key);
$tplObj -> out['down_flow'] = $redis->get($down_flow_key) ? $redis->get($down_flow_key) :0;
$down_num_key = $prefix.":".$active_id.":down_num:".$imsi.":".$today;
$tplObj -> out['down_num'] = $redis->get($down_num_key) ? $redis->get($down_num_key) : 0;
$guide_read_key = $prefix.":".$active_id.":guide_read:".$imsi;
$tplObj -> out['guide_read'] = $redis->get($guide_read_key) ? $redis->get($guide_read_key) : 0;	
$soft_info = get_soft_info();
$tplObj -> out['soft_info'] = $soft_info;
$tplObj -> out['soft_info_json'] = json_encode($soft_info);
$sign_num_key = $prefix.":".$active_id.":sign_num:".$imsi;
$tplObj -> out['sign_num'] =  $redis->get($sign_num_key) ? $redis->get($sign_num_key) : 0;	
$guide_read2_key = $prefix.":".$active_id.":guide_read2:".$imsi.":".$today;
$tplObj -> out['guide_read2'] = $redis->get($guide_read2_key) ? $redis->get($guide_read2_key) : 0;	
$tplObj -> display($tpl);