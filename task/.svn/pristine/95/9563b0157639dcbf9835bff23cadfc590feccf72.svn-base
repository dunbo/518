<?php
include dirname(__FILE__).'/../init.php';
//ini_set('display_errors',true);
//error_reporting(E_ALL);
$db = 'sendnum';
$bit = array('day' => 8, 'ip' => 16, 'uid' => 32, 'sum' => 64);
$worker->addFunction("game_send_number_new", "game_send_number_new_func");  
while ($worker->work());

// main
function game_send_number_new_func($jobs) {
	if ( !($data = json_decode($jobs->workload(),true)) ) {
        return False;
	}
	while (list($key, $val) = each($data)) {
		$$key = $val;	
	}
	$mdkey = 'A'.$active_id.'UID'.$uid.'IP'.$ip.'TM'.$take_tm;
	$sendnum_active = get_active_info($active_id);
	if ($mdval = check_before_get_number($active_id, $sendnum_active, $data)) {
		writeCache($mdkey, $mdval);
		return;	
	}
	if (!($sendnum_valid = get_number_valid($active_id))) {		
		writeCache($mdkey, 2);	
		return;
	}
	writeCache($mdkey, $sendnum_active['active_name'].':'.$sendnum_valid['active_num']);
	$data['id'] = $sendnum_valid['id'];
	update_sendnum_number($data);
	update_sendnum_active($active_id);
}

//获取活动信息
function get_active_info($active_id){
	global $db;
	$model = getInstanceDb();
	$option = array(
		'table' => 'sendnum_active',
		'where' => array('id' => $active_id)
	);
	$result = $model -> findOne($option,$db);
	return $result;
}

function check_before_get_number($active_id, $sendnum_active, $data) {
	global $bit;
	//查询该活动的开始状态
	if ($sendnum_active['start_tm'] > $data['take_tm']) {
		return "ready".date("Y-m-d H:i:s", $sendnum_active['start_tm']);
	}

	//查询该活动的结束状态
	if ($sendnum_active['end_tm'] < $data['take_tm']) {
		return 2;
	}

	//查询该活动的有效状态
	if($sendnum_active['status'] == 0){//已停用
		return 2;
	}

	//查询该活动是否还有剩余号码
	if ($sendnum_active['used_cnt'] >= $sendnum_active['num_cnt']) {
		return 2;	
	}	

	$condition = getConditionByType($sendnum_active['active_type'], $data);	
	$condition['active_id'] = $active_id;
	$condition['status'] = 1;
	$sendnum_count = get_number_info($condition);
	writeCache(getMd5Val($data, $sendnum_active), $sendnum_count['count']);
	if ($sendnum_count['count'] >= $sendnum_active['conf_cnt']) {
		if (($sendnum_active['active_type'] | $bit['day']) == $sendnum_active['active_type'] || $sendnum_active['active_type'] == 1) {
			return 3;	
		} else {
			return 4;
		}
	}	

	return false;
}

function getConditionByType($active_type, $data) {
	global $bit;
	$condition = array();
	if (($active_type | $bit['day']) == $active_type || $active_type == 1) {
		$s_tm = strtotime(date('Y-m-d 00:00:00', $data['take_tm']));	
		$e_tm = strtotime(date('Y-m-d 23:59:59', $data['take_tm']));	
		$condition['take_tm'] = array('exp', '>='.$s_tm.' and take_tm <='.$e_tm);	
	}
	if (($active_type | $bit['ip']) == $active_type || $active_type == 3) 
		$condition['ip'] = $data['ip'];
	if (($active_type | $bit['uid']) == $active_type || $active_type == 4)
		$condition['uid'] = $data['uid'];
	return $condition;
}

function get_number_info($condition) {
	global $db;
	$model = getInstanceDb();
	$option = array(
		'field' => 'count(*) AS count',
		'table' => 'sendnum_number',
		'where' => $condition
	);
	$result = $model->findOne($option,$db);
	return $result;
}

//获取号码
function get_number_valid($active_id) {
	global $db;
	$model = getInstanceDb();
	$option = array(
		'table' => 'sendnum_number',
		'where' => array('active_id' => $active_id, 'status' => 0),
		'order' => 'id desc'
	);
	$info = $model->findOne($option,$db);
	return $info;
}

function update_sendnum_number($param) {
	global $db;
	$model = getInstanceDb();
	$where = array('id' => $param['id']);		
	$update = array(
		'__user_table' => 'sendnum_number',
		'status' => 1,
		'take_tm' => $param['take_tm'],
		'uid' => $param['uid'],
		'ip' => $param['ip']
	);
	$model->update($where, $update, $db);
}

function update_sendnum_active($active_id) {
	global $db;
	$model = getInstanceDb();
	$where = array('active_id' => $active_id);		
	$update = array(
		'__user_table' => 'sendnum_active',
		'used_cnt' => 'used_cnt + 1'
	);
	$model->query('update sendnum_active set used_cnt = used_cnt + 1 where id ='.$active_id, $db);
}

function getMd5Val($data, $sendnum_active) {
	global $bit;	
	$md5_val = date('Y-m-d', $data['take_tm']).'USED'.$data['active_id'].'ID'.$data['uid'].'IP'.$data['ip'];
	$md5_val .= 'AT'.$sendnum_active['active_type'].'CN'.$sendnum_active['conf_cnt'];
    return md5($md5_val);
}

function getCache($key) {
	$cacheObj = getInstanceMemcached();
	return $cacheObj->get($key);
}

function writeCache($key, $val, $time=600) {
	$cacheObj = getInstanceMemcached();
	return $cacheObj->set($key,$val,$time);
}

function getInstanceMemcached() {
	if (!$instanceMemcached) {
		$config = load_config('cache/sendnum_memcached');
		$instanceMemcached = new GoMemcachedCacheAdapter($config);
	}
	return $instanceMemcached;
}

function getInstanceDb() {
	if (!$instanceDb) {
		$instanceDb = new GoModel();
	}
	return $instanceDb;
}
