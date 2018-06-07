<?php

require_once(dirname(realpath(__FILE__)) . '/liaoning_telecom_flux_init.php');

$return_arr = array(
	'status' => 0,
	'msg' => ''
);

// 判断活动时间是否已结束
if ($end_status) {
	exit(json_encode(array('status' => -1, 'msg' => '活动已结束，敬请期待下载活动吧~')));
}

// 判断session是否失效
if (!$imei_status) {
	exit(json_encode(array('status' => -1, 'msg' => '亲，您长时间未操作页面已过期，请退出活动页面重新打开~')));
}

// 判断是否为新注册用户
if (!$new_status) {
	exit(json_encode(array('status' => -1, 'msg' => '亲，本活动仅限全新安装安智市场的用户参加，快去参加其他精彩活动吧~')));
}

// 判断此手机是否参加过
if ($get_flux_status) {
	exit(json_encode(array('status' => -1, 'msg' => '您已领取过流量包啦~')));
}

// 检查参数
$telephone = trim($_POST['telephone']);
if (empty($telephone)) {
	exit(json_encode(array('status' => -1, 'msg' => '参数错误')));
}

// 检查手机号码合法性
if (strlen($telephone) != 11 || !preg_match('/^1[34578]\d{9}$/', $telephone)) {
	exit(json_encode(array('status' => -1, 'msg' => '请输入正确的手机号')));
}

$rkey_telephone = "liaoning_telecom_flux:{$aid}:{$telephone}";//手机号码的参与情况

// 判断此手机号码是否参加过
$telephone_status = check_telephone_status();
if ($telephone_status) {
	exit(json_encode(array('status' => -1, 'msg' => '您已领取过流量包啦~')));
}

// 准备参加，将此imei、telephone分别置为已使用状态
$times = $redis->setx('INCR', $rkey_imei, 1);
if ($times > 1) {
	// 还原
	$redis->setx('INCR', $rkey_imei, -1);
	$return_arr['status'] = -10;
	$return_arr['msg'] = '亲，你已经参加了哦';
	exit(json_encode($return_arr));
}

$times = $redis->setx('INCR', $rkey_telephone, 1);
if ($times > 1) {
	// 还原
	$redis->setx('INCR', $rkey_telephone, -1);
	$redis->setx('INCR', $rkey_imei, -1);
	$return_arr['status'] = -10;
	$return_arr['msg'] = '亲，你已经参加了哦';
	exit(json_encode($return_arr));
}

// 发送接口到辽宁电信接口
$telecom_ret = request_telecom($telephone);
$output_json = $telecom_ret['output_json'];
$errno = $telecom_ret['errno'];

// debug
$output_json = '{"result":0, "message":"参加成功！"}';

$result = json_decode($output_json, true);
$return_code = $result['result'];
$return_msg = $result['message'];

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
	'return_code' => $return_code,
	'return_msg' => $return_msg,
	'output_json' => $output_json,
	'errno' => $errno,
	'key' => 'receive'
);
permanentlog('activity_'.$aid .'.log', json_encode($log_data));


if ($errno == 0 && $return_code == 0) {
	// 接口返回成功
	// 写mysql
	$data = array(
		'imei' => $imei,
		'telephone' => $telephone,
		'__user_table' => 'liaoning_telecom_flux',
	);
	$model->insert($data, 'lottery/lottery');
	// 写缓存
	$redis->set($rkey_imei_telephone, $telephone, $r_cache_time);
} else {
	// 接口返回失败，恢复用户的未参与状态
	$redis->setx('INCR', $rkey_telephone, -1);
	$redis->setx('INCR', $rkey_imei, -1);
}

if ($errno == 0 && $return_code == 0) {
	// 返回的msg重新组装一下
	$status = 0;
	$msg = "参与活动成功！";
} else if ($errno == 0) {
	$status = -1;
	if ($return_code == 1 && $return_msg == '号码归属地为空') {
		$msg = '您的手机号不在活动范围内';
	} else {
		$msg = $return_msg;
	}
} else {
	$status = -1;
	$msg = '出错啦';
}

$ret = array('status' => $status, 'msg' => $msg, 'telephone' => $telephone);
exit(json_encode($ret));

function request_telecom($telephone) {
	// 向辽宁电信发起请求
	$url = 'http://ln146.huilongkj.com/flowdd';
	$qdid = '100014';
	$sqnid = '131';
	$qdpassword = '2w5^2&0<';
	$sign_text = $telephone.$qdid.$qdpassword;
	$sign = md5(strtoupper($sign_text));
	
	$data = array(
		'userid' => $telephone,
		'qdid' => $qdid,
		'sqnid' => $sqnid,
		'sign' => $sign,
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output_json = curl_exec($ch);
	$errno = curl_errno($ch);
	curl_close($ch);
	return array('output_json' => $output_json, 'errno' => $errno);
}

// 判断telephone是否参与过
function check_telephone_status() {
	global $redis, $model, $aid, $imei, $r_cache_time, $rkey_imei, $rkey_telephone, $rkey_imei_telephone;
	
	$times = $redis->get($rkey_telephone);
	if (empty($times) && $times !== 0) {
		// 从mysql中读出来
		$data = array(
			'where' => array(
				'telephone' => $telephone,
			),
			'table' => 'liaoning_telecom_flux'
		);
		$find = $model->findOne($data, 'lottery/lottery');
		if ($find) {
			// 更新hash
			$times = 1;
			$redis->setnx($rkey_telephone, $times);
			$redis->expire($rkey_telephone, $r_cache_time);
		} else {
			// 表里没有
			$times = 0;
			$redis->setnx($rkey_telephone, $times);
			$redis->expire($rkey_telephone, $r_cache_time);
		}
	}
	return $times;
}