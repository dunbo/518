<?php
include dirname(__FILE__).'/../init.php';
#include dirname(__FILE__).'/common.php';
$db = 'sendnum';
$model = new GoModel();
load_helper('task');
$worker = get_task_worker('sendNum');
$worker->addFunction("update_sendnum_db", "update_sendnum_db_func");

while ($worker->work());
function update_sendnum_db_func($jobs) {
	global $model;
	global $db;
	if ( !($p = json_decode($jobs->workload(), true)) ) {
		return False;
	}

	//传参
	$uid = $p['uid'];//论坛uid或者是用户唯一码sn
	$active_id = $p['active_id'];//活动id
	$ip = $p['ip'];//参与者的ip
	$now_time = $p['take_tm'];//参加的时间
	$status = $p['status'];
	$active_num = $p['active_num'];
	$table = 'sendnum_number_' . $active_id;
	$where = array(
		'active_num' => $active_num,		
	);
	if($p['imei'] && $p['mac']){
		$driver_key = md5($p['imei'].$p['mac']);
	}else{
		$driver_key = '1';
	}
	$data = array(
		'__user_table' => $table,
		'status' => $status,
		'ip' => $ip,
		'user_type' => $p['user_type'],
		'user_id' => $uid,
		'take_tm' => $now_time,
		'from' => $p['from'],
		'device_id'=>$driver_key
	);
	$model->update($where, $data, $db);

	list($cnt1, $cnt2) = get_active_usedcnt($active_id, $p['from']);

	$where = array(
		'id' => $active_id,		
	);
	$data = array(
		'__user_table' => 'sendnum_active',
		'cnt'. $p['from'] => $cnt1,
		'used_cnt' => $cnt2
	);
	$model->update($where, $data, $db);
}

function get_active_usedcnt($active_id, $from) {
	global $db;
	$option = array(
		'table' => 'sendnum_number_' . $active_id,
		'where' => array(
			'from' => $from,
			'status' => array(1, 3),
		),
		'field' => 'count(id) as cnt',
	);
	$model = new GoModel();
	$result = $model -> findOne($option,$db);
	$cnt1 = intval($result['cnt']);

	$option = array(
		'table' => 'sendnum_number_' . $active_id,
		'where' => array(
			'status' => array(1, 3),
		),
		'field' => 'count(id) as cnt',
	);
	$result = $model -> findOne($option,$db);
	$cnt2 = intval($result['cnt']);
	return array($cnt1, $cnt2);
}
