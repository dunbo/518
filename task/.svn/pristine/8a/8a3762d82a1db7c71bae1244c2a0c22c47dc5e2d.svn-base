<?php
require_once(dirname(__FILE__).'/../init.php');
ini_set('displays_errors', true);
error_reporting(E_ALL);
define('SINGLE_PUSH', 'single_push');
define('PREFIX', 'SINGLE_PUSH_');
$model = new GoModel();
$start = time();
$worker->addFunction('add_push_log_test', 'add_push_log_test_func');  
while ($worker->work());

function add_push_log_test_func($job)
{
	//echo "1\n";
	global $model;
	$redis = new GoRedisCacheAdapter(load_config("single_push/redis"));
	$push_server = 'pushdb';
	$string = $job->workload();
	if ( !($data = json_decode($string, true)) ) {
		return false;
	}
	$user_pushed = $data['push_id'];
	$device_id = $model->escape_string($data['device_id']);
	$create_at = $data['create_at'];
	
	if (empty($user_pushed) || empty($device_id)) {
		return false;
	}
	//推送记录写缓存
	//删除该device_id对应推送缓存
	$ret = $redis->delete(PREFIX . $device_id);
	if ($ret == false)
		echo date('Y-m-d H:i:s') . " Redis Delete Key {PREFIX}{$device_id} Error!\n";

	//写入device_id上次推送时间缓存
	//初步测试阶段此步骤利用数据库实现
	$model->query("REPLACE INTO user_push_time(device_id, last_push_time) VALUES('$device_id', $create_at)", SINGLE_PUSH);
	
	//推送记录入库
	//推送日志库
	if (is_array($user_pushed)) {
		$sql = 'insert into sj_push_log (`push_id`, `device_id`, `create_at`) values ';
		foreach ($user_pushed as $push_id) {
			$sql .= "('{$push_id}', '{$device_id}', '{$create_at}'),";			
		}
		$sql = preg_replace('/,$/', '', $sql);
	} else {
		$sql = "insert into sj_push_log (`push_id`, `device_id`, `create_at`) values ('{$user_pushed}', '{$device_id}', '{$create_at}');";
	}
	$model->query($sql, SINGLE_PUSH);
	//单条推送计数
	$option = array(
		'where' => array
		(
			$user_pushed => array('exp', 'between min_id and max_id')
		),
		'field' => 'table_name',
		'table' => 'push_table_info'
	);
	$result = $model->findOne($option, SINGLE_PUSH);
	if (empty($result))
	{
		//echo $model->getLastSql() . "\n";
		return false;
	}
	$table = $result['table_name'];
	$where = array(
		'notifyId' => $user_pushed
	);
	$option = array(
		'pushed_times' => array('exp', 'pushed_times+1'),
		'__user_table' => $table
	);
	$model->update($where, $option, SINGLE_PUSH);
	//echo "Done\n";
}
