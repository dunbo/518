<?php
include dirname(__FILE__).'/../init.php';
$aid = 294;
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
$worker->addFunction("schoolseason_lottery", "get_num");
while ($worker->work());

function get_num($jobs){
	global $redis;
	global $gift_base;
	global $model;
	global $aid;
	$ttoday_key = "today_num:lottery_{$aid}";
	$sum = 10;
	$now = date('Ymd');
	$imsi = $jobs->workload();
	if(!$gift_base[$now]){
		$the_time = array('20150908' => array(1,2,5,10,1000),'20150909' => array(1,2,5,10,1000),'20150910' => array(1,2,5,10,1000),'20150911' => array(1,2,5,10,1000),'20150912' => array(1,2,5,10,1000),'20150520' => array(1,2,5,10,1000),'20150913' => array(1,2,5,10,1000),'20150914' => array(1,2,5,10,1000),'20150915' => array(1,2,5,10,1000),'20150916' => array(1,2,5,10,1000),'20150917' => array(1,2,5,10,1000));
		//$the_time = array('20150916' => array(0,1,0,1,1500),'20150917' => array(0,3,0,2,2500),'20150918' => array(0,4,1,3,4000),'20150919' => array(1,8,2,5,7500),'20150920' => array(1,8,3,6,8400),'20150921' => array(1,9,4,7,9300),'20150922' => array(1,10,4,8,10800,),'20150923' => array(1,12,4,9,11800),'20150924' => array(1,13,5,10,12700),'20150925' => array(1,13,6,11,13600),'20150926' => array(2,17,6,13,16600),'20150927' => array(2,18,7,14,17400),'20150928' => array(2,19,8,15,18300),'20150929' => array(2,20,9,16,19100),'20150930' => array(2,20,10,17,20000));
		$option = array(
			'where' => array(
				'create_tm' => array('exp','< strtotime('.$now.')')
			),
			'order' => 'award_level',
			'table' => 'schoolseason_lottery_award',
			'group' => 'award_level',
			'field' => 'count(award_level) as awards,award_level'
		);
		$result = $model -> findAll($option,'lottery/lottery');
		$have_award = array(0,0,0,0,0);
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
	
	$now_num = $redis -> gethash($ttoday_key);
	if(!$now_num[$now]){
		$the_time = array('20150908' => array(1,2,5,10,1000),'20150909' => array(1,2,5,10,1000),'20150910' => array(1,2,5,10,1000),'20150911' => array(1,2,5,10,1000),'20150912' => array(1,2,5,10,1000),'20150520' => array(1,2,5,10,1000),'20150913' => array(1,2,5,10,1000),'20150914' => array(1,2,5,10,1000),'20150915' => array(1,2,5,10,1000),'20150916' => array(1,2,5,10,1000),'20150917' => array(1,2,5,10,1000));
		//$the_time = array('20150916' => array(0,1,0,1,1500),'20150917' => array(0,3,0,2,2500),'20150918' => array(0,4,1,3,4000),'20150919' => array(1,8,2,5,7500),'20150920' => array(1,8,3,6,8400),'20150921' => array(1,9,4,7,9300),'20150922' => array(1,10,4,8,10800,),'20150923' => array(1,12,4,9,11800),'20150924' => array(1,13,5,10,12700),'20150925' => array(1,13,6,11,13600),'20150926' => array(2,17,6,13,16600),'20150927' => array(2,18,7,14,17400),'20150928' => array(2,19,8,15,18300),'20150929' => array(2,20,9,16,19100),'20150930' => array(2,20,10,17,20000));
		$option = array(
			'field' => 'count(award_level) as awards,award_level',
			'group' => 'award_level',
			'order' => 'award_level',
			'table' => 'schoolseason_lottery_award'
		);

		$result = $model -> findAll($option,'lottery/lottery');

		$have_award = array(0,0,0,0,0);
		foreach($result as $key => $val){
			$have_award[$val['award_level'] - 1] = $val['awards'];
		}

		$today_num = array();

		foreach($the_time[$now] as $k=> $v){
			$today_num[] = $v - $have_award[$k];
		}

		$redis -> sethash($ttoday_key,array($now => $today_num),86400);
	}
	
	
	$the_award = lottery($gift_base[$now],$sum);
	$now_num = $redis -> gethash($ttoday_key);
	if($now_num){
		if($now_num[$now][$the_award] > 0 && $the_award <= 5){
			$my_award = $the_award+1;
			$now_num[$now][$the_award] = $now_num[$now][$the_award] - 1;
			$now_num = $redis -> sethash($ttoday_key,$now_num,86400);
			if($my_award == 5){
				$prize_redis = "schoolseason_lottery:gift_{$aid}";
				$gift_num = $redis -> rpop($prize_redis);
				$gift_num = json_decode($gift_num,true);
				$award_data = array(
					'imsi' => $imsi,
					'award_level' => $my_award,
					'create_tm' => time(),
					'update_tm' => time(),
					'gift_num' => $gift_num,
					'status' => 1,
					'__user_table' => 'schoolseason_lottery_award'
				);
				$my_return = array($my_award,$gift_num);
			}elseif($my_award <= 4){
				$award_data = array(
					'imsi' => $imsi,
					'award_level' => $my_award,
					'create_tm' => time(),
					'update_tm' => time(),
					'status' => 0,
					'__user_table' => 'schoolseason_lottery_award'
				);
				$my_return = array($my_award);
			}
			$award_result = $model -> insert($award_data,'lottery/lottery');
			$time = time();
			if($award_result){
				if($my_award <= 4){
					$the_arr = array('imsi' => $imsi,'award_level' => $my_award,'time' => $time,'status' => 0);
					$redis -> sethash("schoolseason_lottery:info_{$imsi}_{$aid}",array($award_result => $the_arr));
				}
			}
		}else{
			$my_return = array(6);
		}
	}else{
		$my_return = array(6);
	}
	return json_encode($my_return);
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


