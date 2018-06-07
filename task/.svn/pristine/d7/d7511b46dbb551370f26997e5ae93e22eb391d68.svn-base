<?php
include dirname(__FILE__).'/../init.php';
$aid = 254;
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
$worker->addFunction("friends_lottery", "get_num");
while ($worker->work());

function get_num($jobs){
	global $redis;
	global $gift_base;
	global $model;
	global $aid;
	$today_key = "today_num:lottery_{$aid}";
	$sum = 6000;
	$now = date('Ymd');
	$imsi = $jobs->workload();
	if(!$gift_base[$now]){
		$the_time = array('20150714' => array(0,0,3,2,100),'20150715' => array(0,0,6,4,200),'20150716' => array(0,0,11,6,300),'20150717' => array(0,0,18,8,400),'20150718' => array(0,0,25,10,500));
		$option = array(
			'where' => array(
				'create_tm' => array('exp','< strtotime('.$now.')')
			),
			'order' => 'award_level',
			'table' => 'friends_lottery_award',
			'group' => 'award_level',
			'field' => 'count(award_level) as awards,award_level'
		);
		$result = $model -> findAll($option,'lottery/lottery');
		$have_award = array(0,0,0,0,0,0);
		foreach($result as $key => $val){
			$have_award[$val['award_level'] - 1] = $val['awards'];
		}
		$gift_base = array();
		$today_num = array();
		foreach($the_time[$now] as $k=> $v){
			$today_num[$k] = $v - $have_award[$k];
		}
		$gift_base[$now] = $today_num;
	}

	$now_num = $redis -> gethash($today_key);
	if(!$now_num[$now]){
		$the_time = array('20150714' => array(0,0,3,2,100),'20150715' => array(0,0,6,4,200),'20150716' => array(0,0,11,6,300),'20150717' => array(0,0,18,8,400),'20150718' => array(0,0,25,10,500));
		$option = array(
			'field' => 'count(award_level) as awards,award_level',
			'group' => 'award_level',
			'order' => 'award_level',
			'table' => 'friends_lottery_award'
		);
		$result = $model -> findAll($option,'lottery/lottery');
		$have_award = array(0,0,0,0,0,0);
		foreach($result as $key => $val){
			$have_award[$val['award_level'] - 1] = $val['awards'];
		}
		$today_num = array();
		foreach($the_time[$now] as $k=> $v){
			$today_num[] = $v - $have_award[$k];
		}
		$redis -> sethash($today_key,array($now => $today_num),86400);
	}
	
	$the_award = lottery($gift_base[$now],$sum);
	$now_num = $redis -> gethash($today_key);
	
	if($now_num){
		if($now_num[$now][$the_award] > 0){
			$my_award = $the_award+1;
			$now_num[$now][$the_award] = $now_num[$now][$the_award] - 1;
			$now_num = $redis -> sethash($today_key,$now_num,86400);
			$award_data = array(
				'imsi' => $imsi,
				'award_level' => $my_award,
				'create_tm' => time(),
				'update_tm' => time(),
				'status' => 0,
				'__user_table' => 'friends_lottery_award'
			);
			$my_return = array($my_award);
			$award_result = $model -> insert($award_data,'lottery/lottery');
			$time = time();
			if($award_result){
				$the_arr = array('imsi' => $imsi,'award_level' => $my_award,'create_tm' => time(),'update_tm' => time(),'status' => 0);
				$redis -> sethash("friends_lottery:info_{$imsi}_{$aid}",$the_arr);
			}
		}else{
			$my_return = array(0);
		}
	}else{
		$my_return = array(0);
	}
	return json_encode($my_return);
}

function lottery($gift_base, $sum) {
    $gift_line = array();
    $nows = 0;
    if(!$gift_base){
        return -1;
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

