<?php
/*
 *   充值排行类活动worker
 */
include dirname(__FILE__).'/../init.php';
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
$worker->addFunction("ranking_gift_work", "get_award");
while ($worker->work());
get_award();
function get_award($jobs){
    global $model;
    global $redis;	
    $redis->pingConn();	
    $param = $jobs->workload();
	$param = json_decode($param,true);	
	$pid = $param['pid'];
	$aid = $param['aid'];
	$option = array(
		'table' => 'gm_lottery_prize',
		'where' => array(
			'pid' => $pid,
		),
	);
	$list = $model->findOne($option,'lottery/lottery');		
	$file = load_config('activity_csv',"lottery") . $list['gift_file'];
	$arr = array();
	$handel = fopen($file, "r");
	$i = 0;
	while (($num_arr = mygetcsv($handel, 1000, ",")) !== FALSE) {
		//标题行不写入
		if ($i != 0) {
			$arr[$i] = $num_arr;
		}
		$i++;
	}
	$newarr = array();
	foreach($arr as $k=>$v){
		if(!$v[2]){
			continue;
		}
		$newarr[$v[2]]['third_text'] = $v[1];
		$newarr[$v[2]]['second_text'] = iconv('gbk', 'utf-8',$v[0]);
		$newarr[$v[2]]['first_text'] = $v[2];
		$option = array(
			'table' => 'gm_virtual_prize',
			'where' => array(
				'pid' => $pid,
				'aid' => $aid,
				'first_text' => $v[2],
			),
		);
		$ret = $model->findOne($option,'lottery/lottery');		
		if(!$ret){
			//添加记录
			$data = array(
					'first_text' => $v[2],
					'create_tm' => time(),
					'update_tm' => time(),
					'third_text' => $v[1],
					'pid' => $pid,
					'aid' => $aid,
					'second_text' => iconv('gbk', 'utf-8',$v[0]),
					'__user_table' => 'gm_virtual_prize'
			);
			$model->insert($data,'lottery/lottery');
		}
	}
	$gift_key = 'ranking_gift_'.$aid.$pid;
	$redis -> delete($gift_key);
	$cache_time = 60*86400;
	$redis -> setlist($gift_key,$newarr);
	$redis -> setx('expire',$gift_key,$cache_time);	
}


