<?php

include_once (dirname(realpath(__FILE__)).'/../../init.php');
include_once ('./hebei.php');
$model = new GoModel();
$act_redis = new GoRedisCacheAdapter(load_config("activation_status/redis"));

$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
session_begin();
$user_imei = strtoupper(trim($_SESSION['USER_IMEI']));
//$user_imei = 1;
$imsi = $_SESSION['USER_IMSI'];
$aid = $_GET['aid'];
if (!$user_imei) {
	$tplObj -> out['status'] = 'err';
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> display("lottery/hebei_telecom/index.html");	
	exit;
}
if($_POST){
	//发送手机验证码
	if($_POST['send'] == 1){
		$find = get_record(array('telephone' => $_POST['mobile_num']));
		if($find){
			exit(json_encode(array('code'=>10237)));
		}
		$ret = http_post_mobile();
		// 记日志
		$data = array(
			'imsi' => $imsi,
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $aid ,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $_GET['sid'],
			'time' => time(),
			'users' => '',
			'uid' => '',	
			'telphone' => $_POST['mobile_num'],
			'result_code' => $ret['result_code'],
			'request_no' => $ret['request_no'],
			'key' => "send_num",
		);
		if(isset($ret['result_code']) && ($ret['result_code'] == 00000 || $ret['result_code'] == 10237)){
			$redis->set("hebei_status:".$user_imei,$_POST['mobile_num'],25*86400);
			set_flux_status($user_imei,$_POST['mobile_num']);
		}
		permanentlog('activity_'.$aid .'.log', json_encode($data));			
		exit(json_encode(array('code'=>$ret['result_code'])));
	}
}else{
	// 记日志
	$data = array(
		"imsi" => $imsi,
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $aid,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $_GET['sid'],
		'time' => time(),
		'users' => '',
		'uid' => '',	
		"telphone" => '',
		"key" => "show_homepage"
	);
	permanentlog('activity_'.$aid.'.log', json_encode($data));	
	$act_stmp = $act_redis->get('IMEI:'. $user_imei);
	if($act_stmp){
		$status = 'old';
	}
	$hebei_status = $redis->get("hebei_status:".$user_imei);
	$find = get_record(array('imei' => $user_imei));
	if($hebei_status > 0 || $find){
		$status = 'for_success';
		if($find){
			$mobile_num = $find['telephone'];
		}else{
			$mobile_num = $hebei_status;
		}
		$tplObj -> out['mobile_num'] = $mobile_num;
	}
	$res = activity_is_stop($aid);
	if($_GET['stop'] == 1 || !$res){
		$status = 'stop';
	}
	$tplObj -> out['aid'] = $aid;
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['status'] = $status;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> display("lottery/hebei_telecom/index.html");	
}

//加密业务受理接口
function http_post_mobile(){
	$para_arr = array();
	$para_arr["request_no"]=time().rand(10000,99999)."1";
	$para_arr["contract_id"]="101357";//合同号
	$para_arr["activity_id"]="102861";//活动id
	$para_arr["plat_offer_id"]="104361";//平台销售id
	$para_arr["service_code"]="FS0001";//服务编码
	$para_arr["phone_id"]= $_POST['mobile_num'];
	$para_arr["channel_id"]="1";
	$para_arr["order_type"]="1";
	$para_arr["order_id"]="0";
	$tele = new Telecomencode();
	$encode_data = $tele->telecom_encode(json_encode($para_arr));
	$out_arr["partner_no"]="102449294";
	$out_arr["code"]=$encode_data;
	$our_str=json_encode($out_arr);
	$url="http://182.140.241.47:8080/fps/flowService.do";	
	$ret = httpGetInfo($url,'',$our_str,"hebei_mobile"); 	
	return json_decode($ret,true);
}

// 参与成功后入库，更新缓存
function set_flux_status($imei,$telephone) {
	global $model;
	$data = array(
		'imei' => $imei,
		'telephone' => $telephone,
		'create_time' => time(),
		'__user_table' => 'hebei_telecom_flux'
	);
	$model->insert($data, 'lottery/lottery');
}
function get_record($where) {
	global $model;
	$option = array(
		'where' => $where,
		'table' => 'hebei_telecom_flux'
	);
	$find = $model->findOne($option, 'lottery/lottery');
	if (empty($find)) {
		return false;
	}
	return $find;
}