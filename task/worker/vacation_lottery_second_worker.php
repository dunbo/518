<?php

/*
    寒假抽奖活动2 worker
*/
include dirname(__FILE__).'/../init.php';
$active_id = 186;
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
$worker->addFunction("vacation_lottery_second", "get_award");
while ($worker->work());

function get_award($jobs){
    global $redis;
    global $model;
    global $active_id;
	global $gift_base;
    $sum = 100000; //评估每日参加活动人数；
    $now = date('Ymd');
    $imsi = $jobs->workload();
    $have_award = "vacation_lottery_second:my_award_{$active_id}";
    $the_award = array('20150206' => array(0,1,0,20,20,50,50,50),'20150207' => array(0,1,0,20,20,50,50,50),'20150208' => array(0,1,0,20,20,50,50,50),'20150209' => array(0,0,1,20,20,50,50,50),'20150210' => array(0,0,1,20,20,50,50,50),'20150211' => array(0,0,1,20,20,50,50,50));
    if(!$gift_base[$now]){
		$my_option = array(
			'where' => array(
				'status' => 1
			),
			'field' => 'sum(award_level) as awards,award_level',
			'order' => 'award_level',
			'table' => 'vacation_lottery_award'
		);
		$my_result = $model -> findAll($my_option,'lottery/lottery');
		$my_award_arr = array(0,0,0,0,0,0,0,0);
		foreach($my_result as $key => $val){
			$my_award_arr[$val['award_level'] - 1] = $val['awards'];
		}
		foreach($the_award[$now] as $key => $val){
			$gift_bases[$key] = $val - $my_award_arr[$key];
		}
		$gift_base[$now] = $gift_bases;
    }
    $the_award = lottery($gift_base[$now],$sum);
    $my_gift = '';
    $surplus_award = $redis -> gethash("vacation_lottery_second:award_{$active_id}");
    if($the_award < 8 && $surplus_award[$the_award]){
        $my_award = $the_award+1;
        $my_award_arr[$the_award] = $my_award_arr[$the_award] + 1;
        $surplus_award[$the_award] = $surplus_award[$the_award] - 1;
        $redis -> sethash("vacation_lottery_second:award_{$active_id}",$surplus_award);
        $redis -> sethash($have_award,$my_award_arr);
		$my_need = array($imsi,$my_award);
		load_helper('task');
		$task_client = get_task_client();
		$award_result = $task_client->doBackground('vacation_lottery_second_award',json_encode($my_need));
        $my_return = array($my_award);
    }else{
        $rand_arr = array(0,0,0,0);
        $gift_first = $redis -> getx("llen","vacation_lottery_second:award_gift_first_{$active_id}");
        $gift_second = $redis -> getx("llen","vacation_lottery_second:award_gift_second_{$active_id}");
        $gift_third = $redis -> getx("llen","vacation_lottery_second:award_gift_third_{$active_id}");
        $gift_forth = $redis -> getx("llen","vacation_lottery_second:award_gift_forth_{$active_id}");
        if(!$gift_first && !$gift_second && !$gift_third && !$gift_forth){
            $my_award = 13;
        }else{
            if($gift_first){
                $rand_arr[0] = 1;
            }
            if($gift_second){
                $rand_arr[1] = 2;
            }
            if($gift_third){
                $rand_arr[2] = 3;
            }
            if($gift_forth){
                $rand_arr[3] = 4;
            }
            for($i=0;$i<4;$i++){
                if($rand_arr[$i]){
                    $start = $rand_arr[$i];
                    break;
                }
            }
            $rand_arr_re = array_reverse($rand_arr);
            for($i=0;$i<4;$i++){
                if($rand_arr_re[$i]){
                    $end = $rand_arr_re[$i];
                    break;
                }
            }

            $my_rand = mt_rand($start,$end);
            if($my_rand == 1){
                $my_gift = $redis -> rpop("vacation_lottery_second:award_gift_first_{$active_id}");
                $my_award = 9;
            }else if($my_rand == 2){
                $my_gift = $redis -> rpop("vacation_lottery_second:award_gift_second_{$active_id}");
                $my_award = 10;
            }else if($my_rand == 3){
                $my_gift = $redis -> rpop("vacation_lottery_second:award_gift_third_{$active_id}");
                $my_award = 11;
            }else if($my_rand == 4){
                $my_gift = $redis -> rpop("vacation_lottery_second:award_gift_forth_{$active_id}");
                $my_award = 12;
            }
			$my_result = json_decode($my_gift,true);
			foreach($my_result as $key => $val){
				$the_gift = $val;
				$the_package = $key;
			}
			$my_need = array($imsi,$my_award,$the_package,$the_gift);
			load_helper('task');
			$task_client = get_task_client();
			$award_result = $task_client->doBackground('vacation_lottery_second_gift',json_encode($my_need));
        }
        $my_return = array($my_award,$my_result);
    }
    return json_encode($my_return);
}


function lottery($gift_base, $sum) {
    $gift_line = array();
    $nows = 0;
    if(!$gift_base){
        return 8;
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
                                                                                     