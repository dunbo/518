<?php
include dirname(__FILE__).'/../init.php';
#include dirname(__FILE__).'/common.php';
$db = 'recent_game';
$model = new GoModel();
load_helper('task');
$worker = get_task_worker('recent_game');
$worker->addFunction("update_recent_game", "update_recent_game_func");

while ($worker->work());
function update_recent_game_func($jobs) {
	global $model;
	global $db;
	if ( !($p = json_decode($jobs->workload(), true)) ) {
		return False;
	}

	//传参
	$uid = $p['uid'];//论坛uid或者是用户唯一码sn
	$table = 'recent_game_' . $uid % 10;
	foreach ($p['update'] as $package => $v) {
		$data = array(
			'__user_table' => $table,
		);
		foreach ($v as $key => $value) {
			$data[$key] = $value;
		}
		$where = array(
			'package' => $package,
		);
		$model->update($where, $data, $db);
	}
	foreach ($p['insert'] as $package => $v) {
		$v['package'] = $package;
		$v['uid'] = $uid;
		$v['__user_table'] = $table;
		$model->insert($v, $db);
	}
}
