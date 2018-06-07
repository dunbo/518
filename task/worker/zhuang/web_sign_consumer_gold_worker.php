<?php
/******2017-4-17
 ******web每日签到】金币购买worker
 */
include dirname(__FILE__).'/../../init.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$gift_base = array();
ini_set('default_socket_timeout', -1);

load_helper('task');
$worker = get_task_worker();
$worker->addFunction("web_sign_consumer_gold", "go_consume");
while ($worker->work());

function go_consume($jobs){
	global $model;
	global $redis;
	$jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
	print_r($jobs); 
	$id = $jobs['water_id'];
	$pid = $jobs['pid'];
	$sid = $jobs['uc_sid'];
	$amount = $jobs['amount'];
	$device_arr = json_decode($jobs['device'],true);
	$header = json_decode($jobs['header'],true);
		
	$data = array(
		'pid' => $pid,
		'sid' => $sid,
		'amount'=>$amount
	);
	$extra = array(
		'prefix'=>'task',
		'apiname'=>'/api/tms/point/reduce',
		'version' => 'v65',
		'passthrough'=>true
	);
	$app = defined('APP_NAME') ? APP_NAME : 'gomarket';
	$ucenter = load_config('ucenter/'.$app, 'uc');
	$request_serviceId = $ucenter['client_serviceId'];
	$apiname = $extra['apiname'];
	$api_info = array(
		'prefix'=>'task',
		'apiname'=> $apiname,
		'passthrough'=> true,
		'sid'=> $sid
	);
	$data['serviceId'] = $request_serviceId;
	$data_array = array(
		'data'=> $data,
		'device'=>$device_arr,
		'header'=>$header
	);
	//file_put_contents("/tmp/my_test_z.log", date('[Y-m-d H:i:s]')."\t".print_r($api_info,true)."\r\n".print_r($data_array,true)."\r\n".print_r($extra,true), FILE_APPEND);
	load_helper('ucenter');		
	$result = request_task($api_info,$data_array, $extra);
	if(empty($result['pid'])){
		$status = 2;
		if($result['code'] == 51036){
			$result['msg'] = "金币不足";
		}
	}else{
		$status = 1;
	}
	$where = array(
		'id' => $id
	);
	$data = array(
		'status' => $status,
		'msg' =>$result['msg'] ? $result['msg'] : '',
		'update_tm' => time(),
		'__user_table' => 'qd_consume'
	);
	$model -> update($where,$data,'lottery/lottery');	
	var_dump($result);
	writelog('web_sign_consumer_gold.log', print_r($api_info, true) . "\n" .print_r($data_array, true) . "\n" .print_r($extra, true) . "\n" . print_r($result, true) . "\n\n");
}

//日志
function writelog($filename,$msg){
	$now = time();
	$path = "/data/att/permanent_log/web_sign_log/".date("Y-m-d", $now);
	if(!file_exists($path)){
		mkdir($path, 0755, true);
	}	
	$path_log = $path."/".$filename;
	$msg = date('Y-m-d H:i:s', $now). " {$msg}\n";
	file_put_contents($path_log, $msg, FILE_APPEND);
}