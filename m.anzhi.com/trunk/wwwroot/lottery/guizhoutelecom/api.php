<?php

include_once (dirname(realpath(__FILE__)).'/init.php');

$return_arr = array(
	'status' => 400,
	'msg' => ''
);

if ($now > $activity_end_time) {
	// 活动已结束
	$return_arr['status'] = -2;
	$return_arr['msg'] = '活动已结束～';
	exit(json_encode($return_arr));
}

if (!$imei_status) {
	// session失效
	$return_arr['status'] = -1;
	$return_arr['msg'] = '亲，您长时间未操作页面已过期，请退出活动页面重新打开~';
	exit(json_encode($return_arr));
}

if (!$new_status) {
	// 判断是否为新用户
	$return_arr['status'] = -3;
	$return_arr['msg'] = '亲，本活动仅限全新安装安智市场的用户参加，快去参加其他精彩活动吧~';
	exit(json_encode($return_arr));
}

if ($flux_status) {
	// 判断是否已领取过
	$return_arr['status'] = -4;
	$return_arr['msg'] = '您已领取过流量包啦~';
	exit(json_encode($return_arr));
}

if (empty($telephone)) {
	$return_arr['status'] = -11;
	$return_arr['msg'] = '请输入手机号';
	exit(json_encode($return_arr));
}
if (!preg_match('/^1[34578][0-9]{9}$/', $telephone)) {
	$return_arr['status'] = -12;
	$return_arr['msg'] = '请输入正确的手机号';
	exit(json_encode($return_arr));
}
// 判断此手机号码是否已领取过
$telephone_status = get_telephone_status();
if ($telephone_status) {
	$return_arr['status'] = -13;
	$return_arr['msg'] = '您已领取过流量包啦~';
	exit(json_encode($return_arr));
}

// 此imei正在领取中
$imei_suspending = $redis->setx('incr', $rkey_imei_suspending, 1);
$redis->expire($rkey_imei_suspending, 300);
if ($imei_suspending > 1) {
	$redis->setx('incr', $rkey_imei_suspending, -1);
	$return_arr['status'] = -13;
	$return_arr['msg'] = '您正领取中，请稍后再试~';
	exit(json_encode($return_arr));
}
// 此telephone正在领取中
$telephone_suspending = $redis->setx('incr', $rkey_telephone_suspending, 1);
$redis->expire($rkey_telephone_suspending, 300);
if ($telephone_suspending > 1) {
	$redis->setx('incr', $rkey_telephone_suspending, -1);
	$redis->setx('incr', $rkey_imei_suspending, -1);
	$return_arr['status'] = -13;
	$return_arr['msg'] = '您正领取中，请稍后再试~';
	exit(json_encode($return_arr));
}

// 向贵州电信发起请求
$api_ret = request_guizhoutelecom_api($telephone);
$content = $api_ret['content'];
$exception = $api_ret['exception'];
if ($content === false) {
	// 出现异常
	$return_arr['status'] = 300;
	$return_arr['msg'] = '很抱歉，您的手机号不在活动范围内。';
} else {
	// 接口有返回
	$xml_content = $content->offerBusinessServiceResult;
	$xml = simplexml_load_string($xml_content);
	$return_code = (string)$xml->result;
	$return_msg = (string)$xml->resultMsg;
	if (!empty($xml) && empty($exception) && $return_code === '0') {
		// 订购成功
		set_flux_status();
		$return_arr['status'] = 200;
		$return_arr['msg'] = '领取成功！';
		$return_arr['telephone'] = $telephone;
	} else {
		// 出错，统一都为以下提示
		$return_arr['status'] = 300;
		$return_arr['msg'] = '很抱歉，您的手机号不在活动范围内。';
	}
}

$redis->setx('incr', $rkey_telephone_suspending, -1);
$redis->setx('incr', $rkey_imei_suspending, -1);

// 记日志
$log_data = array(
	'imsi' => $_SESSION['USER_IMSI'],
	'imei' => $_SESSION['USER_IMEI'],
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid ,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'users' => '',
	'uid' => '',
	'telephone' => $_POST['telephone'],
	'return_code' => $return_code,//电信接口无异常时返回的错误码，0为成功
	'return_msg' => $return_msg,//电信接口无异常时返回的消息
	'output_json' => json_encode($api_ret['content']),//电信接口无异常时的返回内容记录
	'output_exception' => json_encode($api_ret['exception']),//非空时则表示电信接口有异常
	'key' => 'receive'
);
permanentlog('activity_'.$aid .'.log', json_encode($log_data));

exit(json_encode($return_arr));

// 请求贵州电信接口
function request_guizhoutelecom_api($telephone) {
	$ws = 'http://219.151.11.198:8005/CRM/CRMService.asmx?wsdl';
	$client = new SoapClient($ws);
	$post_data = array(
        'UserPhone' => $telephone,
        'TcCode' => '700013211',
        'TcName' => '流量包订购',
        'validNum' => '1D5DC298100F81821A07852C4AD77158F94B2BC2A24F403931A037EC2246CE17651944813E65016B',
    );
	try {
		$content = $client->offerBusinessService($post_data);
		/*
		$content_string = '{"offerBusinessServiceResult":"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<businessServiceResponse><coNbr>510126482050<\/coNbr><serialId>59761085120160104024942949<\/serialId><areaCode>0851<\/areaCode><channelId>59761<\/channelId><staffCode>53081<\/staffCode><result>0<\/result><resCode>CRM-BUSS0000SUCC<\/resCode><resultMsg>511035068520<\/resultMsg><prodMembers\/><\/businessServiceResponse>"}';
		$content = json_decode($content_string);
		*/
	} catch (SoapFault $exception) {
		$content = false;
	}
	$ret = array(
		'content' => $content,
		'exception' => $exception,
	);
	return $ret;
}