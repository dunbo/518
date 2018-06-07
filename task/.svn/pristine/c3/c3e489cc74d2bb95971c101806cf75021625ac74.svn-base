<?php
include dirname(__FILE__).'/../init.php';
$aid = 218;
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
$worker->addFunction("superman_lottery", "get_num");
while ($worker->work());

function get_num($jobs){
	global $redis;
	global $gift_base;
	global $model;
	global $aid;
	$ttoday_key = "today_num:lottery_{$aid}_alter_521";
	$sum = 3000;
	$now = date('Ymd');
	$imsi = $jobs->workload();
	if(!$gift_base[$now]){
		$the_time = array('20150515' => array(0,0,0,4,100,200,7500),'20150516' => array(0,0,0,7,250,360,12000),'20150517' => array(0,0,0,10,400,520,18000),'20150518' => array(0,0,0,13,480,580,20000),'20150519' => array(0,0,1,20,680,780,30000),'20150520' => array(0,1,1,21,760,940,32000),'20150521' => array(0,1,2,28,960,1040,100),'20150522' => array(0,1,2,31,1040,1100,100),'20150523' => array(0,1,2,36,1190,1260,200),'20150524' => array(0,1,2,41,1340,1420,300),'20150525' => array(0,1,2,46,1520,1620,400),'20150526' => array(0,1,2,47,1600,1680,500),'20150527' => array(0,1,2,54,1800,1880,600),'20150528' => array(0,1,3,57,1900,1940,700),'20150529' => array(0,1,3,60,2000,2000,800));
		
		//测试
		//$the_time = array('20150512' => array(0,0,0,4,100,200,1500),'20150513' => array(0,0,0,7,250,360,3000),'20150514' => array(0,0,0,10,400,520,4500),'20150515' => array(0,0,0,13,480,580,5000),'20150516' => array(0,0,1,20,680,780,7500),'20150517' => array(0,1,1,21,760,940,8000),'20150518' => array(0,1,2,28,960,960,10500),'20150519' => array(0,1,2,31,1040,1100,11000),'20150520' => array(0,1,2,36,1190,1260,12500),'20150521' => array(0,1,2,41,1340,1420,14000),'20150522' => array(0,1,2,46,1520,1620,16000),'20150523' => array(0,1,2,47,1600,1680,16500),'20150524' => array(0,1,2,54,1800,1880,19000),'20150525' => array(0,1,3,57,1900,1940,19500),'20150526' => array(0,1,3,60,2000,2000,20000));
		
		$option = array(
			'where' => array(
				'time' => array('exp','< strtotime('.$now.')')
			),
			'order' => 'award_level',
			'table' => 'superman_lottery_award',
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
	
	$now_num = $redis -> gethash($ttoday_key);
	if(!$now_num[$now]){
		$the_time = array('20150515' => array(0,0,0,4,100,200,7500),'20150516' => array(0,0,0,7,250,360,12000),'20150517' => array(0,0,0,10,400,520,18000),'20150518' => array(0,0,0,13,480,580,20000),'20150519' => array(0,0,1,20,680,780,30000),'20150520' => array(0,1,1,21,760,940,32000),'20150521' => array(0,1,2,28,960,1040,100),'20150522' => array(0,1,2,31,1040,1100,100),'20150523' => array(0,1,2,36,1190,1260,200),'20150524' => array(0,1,2,41,1340,1420,300),'20150525' => array(0,1,2,46,1520,1620,400),'20150526' => array(0,1,2,47,1600,1680,500),'20150527' => array(0,1,2,54,1800,1880,600),'20150528' => array(0,1,3,57,1900,1940,700),'20150529' => array(0,1,3,60,2000,2000,800));

		
		//测试
		//$the_time = array('20150512' => array(0,0,0,4,100,200,1500),'20150513' => array(0,0,0,7,250,360,3000),'20150514' => array(0,0,0,10,400,520,4500),'20150515' => array(0,0,0,13,480,580,5000),'20150516' => array(0,0,1,20,680,780,7500),'20150517' => array(0,1,1,21,760,940,8000),'20150518' => array(0,1,2,28,960,960,10500),'20150519' => array(0,1,2,31,1040,1100,11000),'20150520' => array(0,1,2,36,1190,1260,12500),'20150521' => array(0,1,2,41,1340,1420,14000),'20150522' => array(0,1,2,46,1520,1620,16000),'20150523' => array(0,1,2,47,1600,1680,16500),'20150524' => array(0,1,2,54,1800,1880,19000),'20150525' => array(0,1,3,57,1900,1940,19500),'20150526' => array(0,1,3,60,2000,2000,20000));
		
		$option = array(
			'field' => 'count(award_level) as awards,award_level',
			'group' => 'award_level',
			'order' => 'award_level',
			'table' => 'superman_lottery_award'
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

		$redis -> sethash($ttoday_key,array($now => $today_num),86400);
	}
	
	
	$the_award = lottery($gift_base[$now],$sum);
	$now_num = $redis -> gethash($ttoday_key);
	
	if($now_num){
		if($now_num[$now][$the_award] > 0 && $the_award < 7){
			$my_award = $the_award+1;
			$now_num[$now][$the_award] = $now_num[$now][$the_award] - 1;
			$now_num = $redis -> sethash($ttoday_key,$now_num,86400);
			if($my_award >= 4 && $my_award <= 7){
				$prize_redis = "superman_lottery:{$my_award}_{$aid}";
				$package_redis = "superman_lottery:package_{$aid}";
				$gift_num = $redis -> rpop($prize_redis);
				$gift_num = json_decode($gift_num,true);
				$all_package = $redis -> gethash($package_redis);
				$package = $all_package[$my_award];
				$award_data = array(
					'imsi' => $imsi,
					'award_level' => $my_award,
					'time' => time(),
					'package' => $package,
					'gift_num' => $gift_num,
					'status' => 1,
					'__user_table' => 'superman_lottery_award'
				);
				$my_return = array($my_award,$gift_num);
			}elseif($my_award <= 3){
				$award_data = array(
					'imsi' => $imsi,
					'award_level' => $my_award,
					'time' => time(),
					'status' => 0,
					'__user_table' => 'superman_lottery_award'
				);
				$my_return = array($my_award);
			}
			$award_result = $model -> insert($award_data,'lottery/lottery');
			$time = time();
			if($award_result){
				if($my_award <= 3){
					$the_arr = array('imsi' => $imsi,'award_level' => $my_award,'time' => $time,'status' => 0);
					$redis -> sethash("superman_lottery:info_{$imsi}_{$aid}",array($award_result => $the_arr));
				}
			}
		}else{
			$my_return = array(8);
		}
	}else{
		$my_return = array(8);
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

