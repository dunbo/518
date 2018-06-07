<?php
/*
	校园行活动抽奖worker
*/
include dirname(__FILE__).'/../init.php';
$active_id = 179;
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
$worker->addFunction("christmas_lottery", "get_num");
while ($worker->work());

function get_num($jobs){
	global $redis;
	global $gift_base;
	global $model;
	global $active_id;
	$sum = 40000; //评估每日参加活动人数；
	$now = date('Ymd');
	$imsi = $jobs->workload();
	if(!$gift_base[$now]){
		$the_time = array('20141223' => array(0,0,0,1,2,7),'20141224' => array(0,1,0,6,8,20),'20141225' => array(0,2,2,10,18,45),'20141226' => array(0,2,3,11,21,65),'20141227' => array(0,2,4,14,24,77),'20141228' => array(0,3,4,16,30,93),'20141229' => array(0,3,5,19,32,105),'20141230' => array(0,3,6,20,36,125),'20141231' => array(1,4,8,25,44,155),'20150101' => array(1,5,9,28,48,185),'20150102' => array(1,5,10,29,48,190),'20150103' => array(1,5,10,30,50,200));

		$option = array(
			'where' => array(
				'time' => array('exp','< strtotime('.$now.')')
			),
			'order' => 'award',
			'table' => 'christmas_award',
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
	$now_num = $redis -> gethash("today_num:lottery_{$active_id}");
	
	if($now_num){
		if($now_num['today_num'][$the_award] > 0 && $the_award < 6){
			$my_award = $the_award+1;
			$now_num['today_num'][$the_award] = $now_num['today_num'][$the_award] - 1;
			$now_num = $redis -> sethash("today_num:lottery_{$active_id}",$now_num,86400);
			$award_data = array(
				'imsi' => $imsi,
				'award' => $my_award,
				'time' => time(),
				'status' => 0,
				'__user_table' => 'christmas_award'
			);
			$award_result = $model -> insert($award_data,'lottery/lottery');
			$time = time();
			if($award_result){
				$the_arr = array('award' => $my_award,'telphone' => '','name' => '','status' => 0,'time' => $time);
				$redis -> sethash("award_{$imsi}:lottery_{$active_id}",array($award_result => $the_arr));
			}
		}else{
			$my_award = 7;
		}
	}else{
		$my_award = 7;
	}
	return $my_award;
}

function lottery($gift_base, $sum) {
    $gift_line = array();
    $nows = 0;
	if(!$gift_base){
		return 6;
	}
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
