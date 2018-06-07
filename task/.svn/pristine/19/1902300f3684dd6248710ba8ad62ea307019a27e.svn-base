<?php
require_once(dirname(__FILE__) . '/../init.php');
define('SINGLE_PUSH', 'single_push');
load_helper('utiltool');
load_helper('task');
$ip = getServerIp();
if ($ip == load_config('redis/setting/cron_server')) {
	exit;
}
$model = new GoModel();
$worker = get_task_worker('push');
$start = time();
$worker->addFunction('add_push_receipt', 'add_push_receipt_func');
while ($worker->work());

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
				'version_code' => $version_code
			);
			permanentlog('receipt.log', json_encode($logdata));
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
			'version_code' => $version_code
		);
		permanentlog('receipt.log', json_encode($logdata));
	}
	//$model->query($sql, SINGLE_PUSH);
}

