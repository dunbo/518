<?php
include_once(dirname(realpath(__FILE__)) . '/init.php');
$model = load_model('softlist');
$push_list = array(
	'1'=> 'http://118.26.203.17:8080',
	'2'=>'http://118.26.203.30:8080',
	'3'=>'http://118.26.203.16:8080',
);
function getFilter($arr) {
	$filter = array();
	$product = isset($arr['product']) ? $arr['product'] : 1;
	$platform = isset($arr['platform']) ? $arr['platform'] : 1;
	$filter = array(
		'device' => $arr['MODEL_DID'],
		'firmware' => $arr['FIRMWARE'],
		'channel' => $arr['MODEL_CID'],
		'wchannel' => $arr['MODEL_CID'],
		'authorized' => $arr['MODEL_AUTHORIZED'],
		'abi' => $arr['ABI'],
		'product' => $product,
		'platform' => $platform,
		'has_channel_soft' => $arr['has_channel_soft'],
	);
	if(isset($arr['app2sd'])) {
		$filter['app2sd'] = 1;
	}
	if (isset($arr['USER_IMSI'])) {
		$filter['operator'] = substr($arr['USER_IMSI'], 0, 5);
	}
	if (isset($arr['MODEL_OID'])) {
		$filter['model_oid'] = $arr['MODEL_OID'];
	}
	if (empty($arr['device:has_rule'])) {
		unset($filter['device']);
	}

	if (empty($arr['channel:has_rule'])) {
		unset($filter['channel']);
		unset($filter['wchannel']);
	}
	return $filter;
}
$redis= new GoRedisCacheAdapter(load_config('yun/redis'));
$user_prefix = "YUN_USER_";
$key = $_SESSION['USER_NAME'];
$softid = trim($_GET['softid']);
//$min_firmware = trim($_GET['MIN_FIRMWARE']);
$cache = new GoMemcachedCacheAdapter(load_config('session/memcached'));
$phone_model = $redis->getlist($user_prefix.$key);
$result = array();
$session_result = array();
$temp_result = array();
if(!empty($phone_model)) {
	foreach ($phone_model as $k=>$v) {
		$val = explode("_",$v);
		$push_host = $push_list[$val[2]];
		$result[$val[0]] = array($val[0],$val[1],$push_host);
		$temp_result[$val[0]] = array($val[0],$val[1],$val[2]);

	}
	$result = array_filter($result);
	$temp_result = array_filter($temp_result);
	foreach ($temp_result as $f => $p) {
		$s = implode("_",$p);
		$sid = $redis->hget("YUN_SESSION",$s);
		$session_info = $cache->get($sid);
		$info = explode(";",$session_info);
		foreach($info as $ik =>$iv) {
			$sp = explode("|",$iv);
			$session_result[$sp[0]] = unserialize($sp[1]);
		}
		$filter = getFilter($session_result);
		$info = $model->filterSoftId($softid,$filter);
		if(empty($info[0])) {
			unset($result[$f]);
		}
	}
	if(empty($result)) {
		$status = 5;
	} else {
		$status = 200;
	}
}else{
	$status = 0;
}
exit(json_encode(array('status'=>$status,'data'=>$result, 'userName' => $_SESSION['USER_NAME'])));
