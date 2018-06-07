<?php

include_once (dirname(realpath(__FILE__)).'/../init.php');
$model = new GoModel();
$act_redis = new GoRedisCacheAdapter(load_config("activation_status/redis"));
session_begin();
$user_imei = strtoupper(trim($_SESSION['USER_IMEI']));
$imsi = $_SESSION['USER_IMSI'];
$aid = $_GET['aid'];
if (!$user_imei) {
	$tplObj -> out['status'] = 'err';
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> display("lottery/lead_flux.html");	
	exit;
}
if($_POST){
	//发送手机验证码
	if($_POST['send'] == 1){
		$mobile = $_POST['mobile_num'];
		$res = check_mobile_number_area(substr($mobile,0,7));
		if(!$res){
			exit(json_encode(array('code'=>'0','msg'=>'您的手机号不在活动范围内')));
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
			'type' => $ret['result'] == 0 ? 1 : 0 ,
			'msg' => $ret['resultMsg'],
			'key' => "send_num",
		);
		permanentlog('activity_'.$aid .'.log', json_encode($data));			
		if($ret['result'] == 0){
			exit(json_encode(array('code'=>'1')));
		}else{
			exit(json_encode(array('code'=>'0','msg'=>$ret['resultMsg'])));
		}
	}
	//验证码验证
	if($_POST['check_send'] == 1){
		$rets = http_sms_order();
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
			'check_num' => $_POST['check_num'],
			'telphone' => $_POST['mobile_num'],
			'type' => $rets['result'] == 0 ? 1 : 0 ,
			'msg' => $rets['resultMsg'],			
			'olid' => $rets['olId'],			
			'key' => "receive"		
		);	
		permanentlog('activity_'.$aid .'.log', json_encode($data));			
		if($rets['result'] == 0){
			exit(json_encode(array('code'=>'1')));
		}else{
			exit(json_encode(array('code'=>'0','msg'=>$rets['resultMsg'])));
		}		
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
	if($_GET['stop'] == 1){
		$status = 'stop';
	}
	$tplObj -> out['aid'] = $aid;
	$tplObj -> out['sid'] = $_GET['sid'];
	$tplObj -> out['status'] = $status;
	$tplObj -> out['static_url'] = $configs['static_url'];
	$tplObj -> display("lottery/lead_flux.html");
}

//发送信息验证码
function http_post_mobile() {
	$url = 'http://58.223.0.75:8123/crm/smsSend.do';
	$para = array(
		'accNumber' => $_POST['mobile_num'],//手机号码
	);	
	$para = json_encode($para);
	$sign = $_POST['mobile_num']."-106515azsc!@45RFVn";
	$sign = md5($sign);
	$staffCode = -106515;
	$vals = "para=$para&sign=$sign&staffCode=$staffCode";
	$ret = httpGetInfo($url,'',$vals,"mobile"); 
	return json_decode($ret,true);
}
//一次一密业务受理接口
function http_sms_order(){
	$url = 'http://58.223.0.75:8123/crm/smsOrderInterface.do';
	$para = array(
		'accNumber' => $_POST['mobile_num'],//手机号码
		'pricePlanCd' => '300509029840',//销售品id 如果有多个，以英文,号进行分隔
		'code' => $_POST['check_num'],//鉴权验证码
	);
	$para = json_encode($para);
	$sign = $_POST['mobile_num']."-106515azsc!@45RFVn";
	$sign = md5($sign);
	$staffCode = -106515;
	$vals = "para=$para&sign=$sign&staffCode=$staffCode";		
	$ret = httpGetInfo($url,'',$vals,"sms_order"); 	
	return json_decode($ret,true);
}

function check_mobile_number_area($number){
	global $model;
	$option = array(
		'table'=>'mobile_number_area',
		'where' => array(
			'type'=>2,
			'number'=>$number,
		),	
		'field'=>"number",
		'cache_time' => 3600,
	);
	$res = $model -> findOne($option);
	return $res;
}