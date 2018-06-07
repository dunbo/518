<?php

/*
    寒假抽奖活动3 worker
*/
include dirname(__FILE__).'/../init.php';
$active_id = 187;
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
$worker->addFunction("vacation_lottery_third", "get_award");
while ($worker->work());

function get_award($jobs){
    global $redis;
    global $model;
    global $active_id;
    $sum = 100000; //评估每日参加活动人数；
    $now = date('Ymd');
    $my_needs = $jobs->workload();
    $my_need = json_decode($my_needs,true);
    $imsi = $my_need[0];
    $user = $my_need[1];
	$user_id = $my_need[2];
    $have_award = "vacation_lottery_third:my_award_{$active_id}";
    $the_award = array('20150214' => array(0,1,0,20,100,25000),'20150215' => array(0,0,1,20,100,25000),'20150216' => array(0,1,0,20,100,25000));
    if(!$gift_base[$now]){
		$my_option = array(
			'where' => array(
				'status' => 1
			),
			'field' => 'sum(award_level) as awards,award_level',
			'order' => 'award_level',
			'table' => 'vacation_third_award'
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
	$have_user = $redis -> gethash("vacation_lottery_third:user_id_{$active_id}",$user_id);
	if($have_user){
		$the_award = 6;
	}else{
		$redis -> sethash("vacation_lottery_third:user_id_{$active_id}",array($user_id));
		$the_award = lottery($gift_base[$now],$sum);
	}
    
    $my_gift = '';
    $surplus_award = $redis -> gethash("vacation_lottery_third:award_{$active_id}");
    $my_award = $the_award+1;
    if($the_award < 5 && $surplus_award[$the_award]){
        $my_award_arr[$the_award] = $my_award_arr[$the_award] + 1;
        $surplus_award[$the_award] = $surplus_award[$the_award] - 1;
        $redis -> sethash("vacation_lottery_third:award_{$active_id}",$surplus_award);
        $redis -> sethash($have_award,$my_award_arr);
        $award_data = array(
            'imsi' => $imsi,
            'user' => $user,
            'award_level' => $my_award,
            'name' => '',
            'telphone' => '',
            'address' => '',
            'time' => time(),
            'status' => 0,
            '__user_table' => 'vacation_third_award'
        );
        $award_result = $model -> insert($award_data,'lottery/lottery');
        if($award_result){
            $my_return = array($my_award);
        }
    }else if($the_award == 5){
        $my_results = $redis -> rpop("vacation_lottery_third:award_gift_{$active_id}");
        $my_result = json_decode($my_results,true);
        foreach($my_result as $key => $val){
            $the_gift = $val;
            $the_package = $key;
        }
        $award_data = array(
            'imsi' => $imsi,
            'user' => $user,
            'award_level' => $my_award,
            'package' => $the_package,
            'gift' => $the_gift,
            'time' => time(),
            'status' => 2,
            '__user_table' => 'vacation_third_award'
        );
        $award_result = $model -> insert($award_data,'lottery/lottery');
        $my_return = array($my_award,$my_result);
    }else if($the_award > 5 || !$surplus_award[$the_award]){
        $my_return = array(7);
    }
    echo json_encode($my_return);
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
