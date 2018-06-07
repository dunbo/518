<?php
/*
	校园行活动抽奖worker
*/
include dirname(__FILE__).'/../init.php';
$active_id = 167;
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
$worker->addFunction("school_lottery", "get_num");
while ($worker->work());

function get_num($jobs){
	global $redis;
	global $gift_base;
	global $model;
	global $active_id;
	$sum = 50000; //评估每日参加活动人数；
	$now = date('Ymd');
	$imsi = $jobs->workload();
	if(!$gift_base[$now]){
		$the_time = array('20141030' => array(0,0,1,2,10,20),'20141031' => array(0,1,2,14,70,140),'20141101' => array(1,1,3,20,100,200),'20141102' => array(1,1,3,24,120,240),'20141103' => array(1,1,3,40,200,400),'20141104' => array(1,1,3,41,205,410),'20141105' => array(1,1,3,42,210,420),'20141106' => array(1,1,3,43,215,430),'20141107' => array(1,1,3,44,220,440),'20141108' => array(1,1,3,45,225,450),'20141109' => array(1,1,3,46,230,460),'20141110' => array(1,1,3,47,235,470),'20141111' => array(1,1,3,48,240,480),'20141112' => array(1,1,3,49,245,490),'20141113' => array(1,1,3,50,250,500),'20141114' => array(1,1,3,51,255,510),'20141115' => array(1,1,3,52,260,520),'20141116' => array(1,1,3,53,265,530),'20141117' => array(1,1,3,54,270,540),'20141118' => array(1,1,3,56,280,560),'20141119' => array(1,1,3,58,290,580),'20141120' => array(1,1,3,60,300,600),'20141121' => array(1,2,4,67,335,670),'20141122' => array(1,2,5,72,360,720),'20141123' => array(1,2,5,76,380,760),'20141124' => array(1,2,5,78,390,780),'20141125' => array(1,2,5,80,400,800),'20141126' => array(1,2,5,82,410,820),'20141127' => array(1,2,5,84,420,840),'20141128' => array(1,2,5,86,430,860),'20141129' => array(1,2,5,88,440,880),'20141130' => array(1,2,5,90,450,900),'20141201' => array(1,2,5,91,455,910),'20141202' => array(1,2,5,92,460,920),'20141203' => array(1,2,5,93,465,930),'20141204' => array(1,2,5,94,470,940),'20141205' => array(1,2,5,95,475,950),'20141206' => array(1,2,5,96,480,960),'20141207' => array(1,1,5,97,485,970),'20141208' => array(1,2,5,98,490,980),'20141209' => array(1,2,5,99,495,990),'20141210' => array(1,2,5,100,500,1000),'20141211' => array(1,2,5,100,500,1000),'20141212' => array(1,2,5,100,500,1000),'20141213' => array(1,2,5,100,500,1000),'20141214' => array(1,2,5,100,500,1000),'20141215' => array(1,2,5,100,500,1000),'20141216' => array(1,2,5,100,500,1000),'20141217' => array(1,2,5,100,500,1000),'20141218' => array(1,2,5,100,500,1000),'20141219' => array(1,2,5,100,500,1000),'20141220' => array(1,2,5,100,500,1000));
		$option = array(
			'where' => array(
				'time' => array('exp','< strtotime('.$now.')')
			),
			'order' => 'award',
			'table' => 'school_award',
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
				'__user_table' => 'school_award'
			);
			$award_result = $model -> insert($award_data,'lottery/lottery');
			$time = time();
			if($award_result){
				if($my_award <= 3){
					$the_arr = array('award' => $my_award,'telphone' => '','name' => '','address' => '','status' => 0,'time' => $time);
					$redis -> sethash("award_{$imsi}:lottery_{$active_id}",array($award_result => $the_arr));
				}elseif($my_award >= 4 && $my_award <= 6){
					$the_arr = array('award' => $my_award,'telphone' => '','status' => 0,'time' => time());
					$redis -> sethash("award_{$imsi}:lottery_{$active_id}",array($award_result => $the_arr));
				}
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
