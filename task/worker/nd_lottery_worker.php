<?php

include dirname(__FILE__).'/../init.php';
$config = load_config('lottery_cache/redis','lottery');
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
$worker->addFunction("nd_lottery", "get_num");

while ($worker->work());

function get_num($jobs){
	global $redis;
	global $gift_base;
	global $model;
	$sum = 88247; //评估每日参加活动人数；
	$now = date('Y-m-d');
	$imsi = $jobs->workload();
	$the_time = array('2014-09-29' => array(0,1,1,3,15,30),'2014-09-30' => array(0,2,2,6,30,60),'2014-10-01'=>array(1,2,3,9,45,90),'2014-10-02' => array(1,2,4,12,60,120),'2014-10-03'=>array(1,2,5,14,70,140),'2014-10-04'=>array(1,2,5,16,80,160),'2014-10-05'=>array(1,2,5,18,90,180),'2014-10-06'=>array(1,2,5,20,100,200),'2014-10-07'=>array(1,2,5,20,100,200));

	if(!$gift_base[$now]){
		$option = array(
			'where' => array(
				'time' => array('exp','< strtotime('.$now.')')
			),
			'order' => 'award',
			'table' => 'nd_award',
			'group' => 'award',
			'field' => 'count(award) as awards,award'
		);
		$result = $model -> findAll($option,'lottery/lottery');
		$have_award = array(0,0,0,0,0,0);
		foreach($result as $key => $val){
			$have_award[$val['award'] - 1] = $val['awards'];
		}

		$gift_base = array();
		$today_num = array();
	
		foreach($the_time[$now] as $k=> $v){
			$today_num[$k] = $v - $have_award[$k];
		}
		$gift_base[$now] = $today_num;
	}
	$the_award = lottery($gift_base[$now],$sum);
	$now_num = $redis -> gethash('today_num:lottery');
	if($now_num){
		if($now_num['today_num'][$the_award] > 0 && $the_award < 6){
			$my_award = $the_award+1;
			$now_num['today_num'][$the_award] = $now_num['today_num'][$the_award] - 1;
			$now_num = $redis -> sethash('today_num:lottery',$now_num,86400);
			$award_data = array(
				'imsi' => $imsi,
				'award' => $my_award,
				'time' => time(),
				'status' => 0,
				'__user_table' => 'nd_award'
			);
			$award_result = $model -> insert($award_data,'lottery/lottery');
			
		}else{
			$my_award = 7;
		}
	}
	return $my_award;
}

function lottery($gift_base, $sum) {
    $gift_line = array();
    $nows = 0;
    foreach ($gift_base as $v) {
        $gift_line[] = array($nows+1, $nows+$v);
        $nows += $v;
    }
    $rand = mt_rand(1, $sum);
    foreach ($gift_line as $k => $v) {
        if ($rand >= $v[0] && $rand <= $v[1]) {
            return $k;
        }
    }
    return $k+1;
}