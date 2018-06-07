<?php
require_once(dirname(__FILE__).'/../init.php');
ini_set('displays_errors', true);
error_reporting(E_ALL);
$ip = getServerIp();
if ($ip == load_config('redis/setting/cron_server')) {
//    exit;
}
load_helper('utiltool');
load_helper('task');
$_SERVER['HTTP_HOST'] = 'push.anzhi.com';
define('SINGLE_PUSH', 'single_push');
define('PREFIX', 'SINGLE_PUSH_');
$model = new GoModel();

function add_push_log_func($job)
{
	global $model;
	$redis = new GoRedisCacheAdapter(load_config("single_push/redis"));
	$push_server = 'pushdb';
	$string = $job->workload();
	if ( !($data = json_decode($string, true)) ) {
		return false;
	}
	$user_pushed = $data['push_id'];
	if (!is_array($user_pushed))
		$user_pushed = array($user_pushed);
	$single_flag = false;
	foreach ($user_pushed as $v)
	{
		$id = explode(',', $v);
		$push_id = $id[0];
		if ($push_id > 1000000)
			$single_flag = true;
	}
	$device_id = $model->escape_string($data['device_id']);
	$create_at = $data['create_at'];
	$source = isset($data['source']) ? $data['source'] : 0;
	$version_code = isset($data['version_code']) ? $data['version_code'] : 0;
	$hook_package = isset($data['hook_package']) ? $data['hook_package'] : 'cn.goapk.market';
	$cid = $data['cid'];
	$INIT_LOGIN_TMS = $data['INIT_LOGIN_TMS'];
	$push_sdk = $data['push_sdk'];

	if (empty($user_pushed) || empty($device_id)) {
		return false;
	}
	//记录日志到文件push.log

	//推送记录写缓存
	//删除该device_id对应推送缓存
	$ret = $redis->delete(PREFIX . $device_id);
	if ($ret == false && $single_flag)
		echo date('Y-m-d H:i:s') . " Redis Delete Key {PREFIX}{$device_id} Error!\n";

	//写入device_id上次推送时间缓存
	//初步测试阶段此步骤利用数据库实现
	//$model->query("INSERT INTO user_push_time VALUES ('$device_id', $create_at) ON DUPLICATE KEY UPDATE last_push_time=$create_at" ,SINGLE_PUSH);
	//$model->query("REPLACE INTO user_push_time(device_id, last_push_time) VALUES('$device_id', $create_at)", SINGLE_PUSH);

	//推送记录入库
	//推送日志库
	if (is_array($user_pushed)) {
		$sql = 'insert into sj_push_log (`push_id`, `device_id`, `create_at`, `source`, `beid`, `version_code`) values ';
		foreach ($user_pushed as $v) {
			$id = explode(",", $v);
			$push_id = isset($id[0]) ? $id[0] : '';
			$beid = isset($id[1]) ? $id[1] : '0';
			$sql .= "('{$push_id}', '{$device_id}', '{$create_at}', {$source}, {$beid}, {$version_code}),";
			$tolog = array(
				'push_id' => $push_id,
				'device_id' => $device_id,
				'create_at' => $create_at,
				'beid' => $beid,
				'source' => $source,
				'version_code' => $version_code,
				'hook_package' => $hook_package,
				'cid' => $cid,
				'push_sdk' => $push_sdk,
				'INIT_LOGIN_TMS' => $INIT_LOGIN_TMS,
			);
			$new_push = isNewPush($push_id);
			if($new_push) {
				permanentlog('yinxing_push.log', json_encode($tolog));	
			} else {
				permanentlog('push.log', json_encode($tolog));
			}
		}
		$sql = preg_replace('/,$/', '', $sql);
	} else {
		$id = explode(",", $user_pushed);
		$push_id = isset($id[0]) ? $id[0] : '0';
		$beid = isset($id[1]) ? $id[1] : '';
		$sql = "insert into sj_push_log (`push_id`, `device_id`, `create_at`, `source`, `beid`, `version_code`) values ('{$push_id}', '{$device_id}', '{$create_at}', {$source}, {$beid}, {$version_code});";
		$tolog = array(
			'push_id' => $push_id,
			'device_id' => $device_id,
			'create_at' => $create_at,
			'beid' => $beid,
			'source' => $source,
			'version_code' => $version_code,
			'hook_package' => $hook_package,
			'cid' => $cid,
			'push_sdk' => $push_sdk,
			'INIT_LOGIN_TMS' => $INIT_LOGIN_TMS,		
		);
		$new_push = isNewPush($push_id);
		if ($new_push) {
			permanentlog('yinxing_push.log', json_encode($tolog));
		} else {
			permanentlog('push.log', json_encode($tolog));
		}
	}
	//$model->query($sql, SINGLE_PUSH);
	//单条推送计数
	foreach($user_pushed as $push_id)
	{
		$id = explode(',', $push_id);
		$push_id = $id[0];
		if ($push_id < 1000000)
			continue;
		$option = array(
			'where' => array
			(
				$push_id => array('exp', 'between min_id and max_id'),
				'status' => 1
			),
			'field' => 'table_name',
			'table' => 'push_table_info'
		);
		$result = $model->findOne($option, SINGLE_PUSH);
		if (empty($result))
		{
			file_put_contents('/tmp/push_worker_error.log', $push_id . " table not find\n");
			continue;
		}
		$table = $result['table_name'];
		$where = array(
			'notifyId' => $push_id
		);
		$option = array(
			'pushed_times' => array('exp', 'pushed_times+1'),
			'__user_table' => $table
		);
		$model->update($where, $option, SINGLE_PUSH);
	}
}

function add_push_receipt_func($job)
{
	global $model;
	$_SERVER['HTTP_HOST'] = 'push.anzhi.com';
	$logdata = array();
	$string = $job->workload();
	if ( !($data = json_decode($string, true)) ) {
		return false;
	}
	$user_pushed = $data['push_id'];
	$device_id = $model->escape_string($data['device_id']);
	$create_at = $data['create_at'];
	$cid = $data['cid'];
	$INIT_LOGIN_TMS = $data['INIT_LOGIN_TMS'];
	$push_sdk = $data['push_sdk'];
	
	$day = date('Ymd', $create_at);
		$version_code = isset($data['version_code']) ? $data['version_code'] : 0;

	if (empty($user_pushed) || empty($device_id)) {
		return false;
	}

	if (is_array($user_pushed)) {
		$sql = 'insert into sj_push_receipt (`push_id`, `device_id`, `create_at`, `beid`, `version_code`) values ';
		foreach ($user_pushed as $v) {
			$id = explode(",", $v);
			$push_id = isset($id[0]) ? $id[0] : '';
			$beid = isset($id[1]) ? $id[1] : '0';
			$sql .= "('{$push_id}', '{$device_id}', '{$create_at}', {$beid}, {$version_code}),";
			$logdata = array(
				'push_id' => $push_id,
				'device_id' => $device_id,
				'beid' => $beid,
				'create_at' => $create_at,
				'version_code' => $version_code,
				'cid' => $cid,
				'push_sdk' => $push_sdk,
				'INIT_LOGIN_TMS' => $INIT_LOGIN_TMS,					
			);
			$new_push = isNewPush($push_id);
			if ($new_push) {
				permanentlog('yinxing_receipt.log', json_encode($logdata));
			} else {
				permanentlog('receipt.log', json_encode($logdata));
			}
		}
		$sql = preg_replace('/,$/', '', $sql);

	} else {
		$id = explode(",", $user_pushed);
		$push_id = isset($id[0]) ? $id[0] : '';
		$beid = isset($id[1]) ? $id[1] : '0';
		$sql = "insert into sj_push_receipt (`push_id`, `device_id`, `create_at`, `beid`, `version_code`) values ('{$push_id}', '{$device_id}', '{$create_at}', {$beid}, {$version_code});";
		$logdata = array(
			'push_id' => $push_id,
			'device_id' => $device_id,
			'beid' => $beid,
			'create_at' => $create_at,
			'version_code' => $version_code,
			'cid' => $cid,
			'push_sdk' => $push_sdk,
			'INIT_LOGIN_TMS' => $INIT_LOGIN_TMS,			
		);
		$new_push = isNewPush($push_id);
		if ($new_push) {
			permanentlog('yinxing_receipt.log', json_encode($logdata));
		} else {
			permanentlog('receipt.log', json_encode($logdata));
		}
	}
	//$model->query($sql, SINGLE_PUSH);
}

function isNewPush($pushid)
{
	global $model;
	$option = array(
		'table' => 'sj_market_push',
		'where' => array(
			'id' => $pushid
		),
		'cache_time' => 3600,
	);
	$push = $model->findOne($option);
	if ($push['push_type'] == 4) {
		return true;
	}

	return false;
}
