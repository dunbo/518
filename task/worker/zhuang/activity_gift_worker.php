<?php
/*
 *   活动礼包生成worker
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
$worker->addFunction("activity_gift_worker", "get_award");
while ($worker->work());
get_award();
function get_award($jobs){
    global $model;
    global $redis;	
    $param = $jobs->workload();
	$param = json_decode($param,true);	
	$pid = $param['pid'];
	$aid = $param['aid'];
	$table = $param['table'];
	$gift_table = $param['gift_table'];
	$option = array(
		'table' => $table,
		'where' => array(
			'id' => $pid,
			'aid' => $aid,
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
	$data_arr = array_chunk($arr,500);
	$prefix = "activity_gift";		
	$model -> query("DELETE FROM {$gift_table} WHERE aid={$aid} and pid={$pid}",'lottery/lottery');
	//激活码入口
	foreach ($data_arr as $key => $val) {
		if ($val) {
			$time = time();
			$sql_str = '';
			$softname = '';
			foreach($val as $v){
				if(empty($v)) continue;
				$softname = iconv('gbk', 'utf-8',$v[0]);
				$sql_str .= ",({$aid},{$pid},'{$v[2]}','{$time}','{$time}','{$v[1]}','{$softname}')";
			}
			$sql_str =  substr($sql_str,1);
			$sql = "INSERT INTO {$gift_table} (`aid`,`pid`,`gift_number`,`create_tm`,`update_tm`,`package`,`softname`) VALUES ".$sql_str;
			$affect = $model -> query($sql,'lottery/lottery');
		}
	}
	get_gift($prefix,$aid,$pid,$gift_table);
}
//刷礼包缓存
function get_gift($prefix,$aid,$pid,$gift_table){
	global $model;
	global $redis;	
    $redis->pingConn();		
	$pkg_key = "{$prefix}:{$aid}:{$pid}:pkg";
	$ip = getServerIp();
	if($ip == '192.168.1.242'){
		$ip = '192.168.1.242';
		$port = 6379;		
	}else{
		$ip = '192.168.1.151';
		$port = 6380; 
	}
	shell_exec("/usr/local/bin/redis-cli -h {$ip} -p {$port}  keys  '{$prefix}:{$aid}:{$pid}:*' | xargs /usr/local/bin/redis-cli -h {$ip}  -p {$port} DEL");	
	$prize_gift_pkg = $redis->get($pkg_key);
	if(!$prize_gift_pkg){
		$limit = 1000;
		$start = 0;
		$cache_time = 60*86400;
		$prize_gift_pkg = array();		
		for($start=0;;$start++){
			$option = array(
				'table' => $gift_table,
				'where' => array( 
					'status' => 0 ,
					'aid' => $aid,
					'pid' => $pid
				),
				'limit' => $limit,
				'offset' => $start*$limit,
			);
			$list = $model->findAll($option,'lottery/lottery');	
			//echo $model->getSql()."\n";
			if(!$list) break;
			$prize_gift = array();
			foreach($list as $k => $v){
				$prize_gift_pkg[$v['package']] = 1;
				$prize_gift[$v['package']][] = $v;				
			}
			$redis->set($pkg_key,array_keys($prize_gift_pkg),$cache_time);
			foreach($prize_gift as $k => $v){
				$gift_key = "{$prefix}:{$aid}:{$pid}:gift_num:".$k;
				$redis -> setlist($gift_key,$v);
				$redis -> setx('expire',$gift_key,$cache_time);
				//$redis -> expire($gift_key,86400*60);				
			}
		}
	}	
	return $prize_gift_pkg;		
}


