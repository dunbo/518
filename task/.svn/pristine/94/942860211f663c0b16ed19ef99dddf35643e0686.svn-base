<?php
/*
 *   1元购worker(2016年劳动节活动)
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
$worker->addFunction("labor_day_2016_lottery", "get_award");
while ($worker->work());
//get_award();

function get_award($jobs){
	global $model;
	global $redis;
    $jobs= $jobs->workload();
	$jobs = json_decode($jobs,true);
	print_r($jobs);	
	$start_time = $jobs['start_time'];
	$pid = $jobs['pid'];
	$aid = $jobs['aid'];
	$redis->pingConn();
	$option = array(
		'where' => array(
			'status' => 0,
			'pid' => $pid,
			'aid' => $aid,
			'start_time' => $start_time,
		),
		'table' => 'one_dollar_kind_award',
	);
	$prize_arr= $model->findAll($option,'lottery/lottery');
	foreach($prize_arr as $v){
		$rs = explode('/',$v['probability']);
		if(!isset($basenum)){
			$basenum = $rs[1];
			continue;
		}else{
			$basenum = min_multiple($basenum,$rs[1]);
		}
	}

	foreach($prize_arr as $vvv){
		$rs = explode('/',$vvv['probability']);
		$gift_base[$vvv['uid']] = $rs['0']/$rs['1']*$basenum;
	}
	$uid= lottery($gift_base,$basenum);

	if($uid!=-1){
		$where = array(
			'status' => 0,
			'pid' => $pid,
			'aid' => $aid,
			'uid' => array('exp','!="'.$uid.'"'),
			'start_time' => $start_time,
		);
		$data = array(
			'status' => 2,
			'lottery_time' => time(),
			'__user_table' => 'one_dollar_kind_award'
		);
		$model -> update($where,$data,'lottery/lottery');

		$where = array(
			'status' => 0,
			'pid' => $pid,
			'uid' => $uid,
			'aid' => $aid,
			'start_time' => $start_time,
		);
		$data = array(
			'status' => 1,
			'lottery_time' => time(),
			'__user_table' => 'one_dollar_kind_award'
		);
		$model -> update($where,$data,'lottery/lottery');

		//要返回，记日志
		$resarr = array();
		$resarr['pid'] = $pid;
		$resarr['uid'] = $uid;
		$resarr['start_time'] = $start_time;
		print_r($resarr);
		return json_encode($resarr);
	}
	echo 'false';
	return false;
}

function min_multiple($a,$b)  //最小公倍数
{
     $m = max($a,$b);
     $n = min($a,$b);
     for($i=1; ; $i++)
     {
         if (is_int($m*$i/$n))
         {
            return $mix=$m*$i;
         }
     }
}

function lottery($gift_base, $sum) {
    $gift_line = array();
    $nows = 0;
    if(!$gift_base){
        return -1;
    }

    foreach ($gift_base as $k=>$v) {
        $gift_line[$k] = array($nows+1, $nows+$v);
        $nows += $v;
    }

    $rand = mt_rand(1, $sum);

    foreach ($gift_line as $k => $v) {
        if ($rand >= $v[0] && $rand <= $v[1]) {
            return $k;
        }
    }
    return -1;
}
