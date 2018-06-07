<?php
/*
** 叫醒春天活动抽奖worker
*/
include dirname(__FILE__).'/../init.php';
$active_id = 189;
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
$gift_base = array();

// 奖品配置
$award_level_option = array(
    'where' => array(
        'config_type' => 'SPRING_LOTTERY_AWARD',
        'status' => 1
    ),
    'cache_time' => 86400,
    'table' => 'pu_config'
);
$award_level_result = $model -> findOne($award_level_option);
$award_config = json_decode($award_level_result['configcontent'],true);
// 奖品等级个数
$award_level_count = count($award_config);

// 每天不同奖的个数
$each_day_award = array(
    '20150202' => array(2,0,0,0,0,0,0),
    '20150203' => array(1,0,0,0,0,0,0),
    '20150204' => array(0,0,0,0,0,0,5),
    '20150205' => array(0,0,0,0,0,0,5),
    '20150206' => array(0,0,0,0,0,5,10),
    '20150207' => array(0,0,0,1,3,5,0),
    '20150208' => array(0,0,0,0,0,0,5),
    '20150209' => array(0,0,0,0,0,0,5),
    '20150210' => array(0,0,0,0,0,0,5),
    '20150211' => array(0,0,0,0,1,0,3),
    '20150212' => array(0,0,0,0,0,0,5),
    '20150213' => array(0,0,0,1,0,5,5),
    '20150214' => array(0,1,1,1,0,1,0),
    '20150215' => array(0,0,0,0,0,1,4),
    '20150216' => array(0,0,0,0,1,1,3),
    '20150217' => array(0,0,0,0,0,0,5),
    '20150218' => array(0,0,1,1,0,0,5),
    '20150219' => array(1,0,0,1,0,5,5),
    '20150220' => array(0,0,0,0,1,3,3),
    '20150221' => array(0,0,0,0,0,0,5),
    '20150222' => array(0,0,0,0,1,1,3),
    '20150223' => array(0,0,0,0,0,1,0),
    '20150224' => array(0,0,0,0,1,1,3),
    '20150225' => array(0,0,0,0,0,1,3),
    '20150226' => array(0,0,0,0,1,0,3),
    '20150227' => array(0,0,0,0,0,0,5),
    '20150228' => array(0,0,0,0,1,0,5),
);

// 算出下一天累计奖品总数
$the_time = array();
$mount_award_num = array();
for ($i = 0; $i < $award_level_count; $i++) {
    $mount_award_num[$i] = 0;
}

foreach ($each_day_award as $day => $award_num) {
    if (count($award_num) != $award_level_count) {
        var_dump("award_num wrong");
        exit;
    }
    foreach ($award_num as $key => $value) {
        if (!isset($mount_award_num[$key])) {
            var_dump("award_num wrong");
            exit;
        }
        $mount_award_num[$key] += $value;
    }
    $the_time[$day] = $mount_award_num;
}

var_dump($the_time);

ini_set('default_socket_timeout', -1);
load_helper('task');
$worker = get_task_worker();
$worker->addFunction("spring_lottery", "get_num");
while ($worker->work());

function get_num($jobs){
    global $redis;
	global $gift_base;
	global $model;
	global $active_id;
    global $the_time;
    global $award_level_count;//中奖等级数量
    $sum = 40000; //评估每日参加活动人数；
	$now = date('Ymd');//当天日期
	$imsi = $jobs->workload();
    var_dump($gift_base);
    if(!$gift_base[$now]) {
        // 统计出已有多少人中奖
        $option = array(
			'where' => array(
				'time' => array('exp','< unix_timestamp('.$now.')')
			),
			'order' => 'award',
			'table' => 'spring_lottery_award',
			'group' => 'award',
			'field' => 'count(award) as awards,award'
		);
		$result = $model -> findAll($option,'lottery/lottery');
        $have_award = array();
        for ($i = 0; $i < $award_level_count; $i++) {
            $have_award[$i] = 0;
        }
		foreach($result as $key => $val){
			$have_award[$val['award'] - 1] = $val['awards'];
		}
        $gift_base = array();
		$today_num = array();
		foreach($the_time[$now] as $k=> $v){
			$today_num[$k] = $v - $have_award[$k];
		}
        foreach ($today_num as $k => $v) {
            if (!$v || $v < 0) {
                $today_num[$k] = 0;
            }
        }
		$gift_base[$now] = $today_num;
    }
    
    // 昨天为止奖品剩余数gift_base[$now]走概率去抽奖
    $the_award = lottery($gift_base[$now], $sum);
    if ($the_award >= $award_level_count) {
        $my_award = $award_level_count + 1;
        return $my_award;
    }
    // 中概率了，但查看是否有足够的奖剩余，没有的话还是没中奖
    // 统计出已有多少人中奖
    $now_specific = time();
    $option = array(
        'order' => 'award',
        'table' => 'spring_lottery_award',
        'group' => 'award',
        'field' => 'count(award) as awards,award'
    );
    $result = $model -> findAll($option,'lottery/lottery');
    $have_award = array();
    for ($i = 0; $i < $award_level_count; $i++) {
        $have_award[$i] = 0;
    }
    foreach($result as $key => $val){
        $have_award[$val['award'] - 1] = $val['awards'];
    }
    $gift_base = array();
    $today_num = array();
    foreach($the_time[$now] as $k=> $v){
        $today_num[$k] = $v - $have_award[$k];
    }
    $gift_base[$now] = $today_num;
    if ($gift_base[$now][$the_award] <= 0) {
        // 实际此奖项已被中完了
        $my_award = $award_level_count + 1;
        return $my_award;
    }
    
    // 中了概率且奖项有剩余
    $my_award = $the_award+1;
    
    // 更新一下$gift_base[$now][$the_award]数量
    $gift_base[$now][$the_award] -= 1;
    
    // 将此imsi获奖记录写表
    $award_data = array(
        'imsi' => $imsi,
        'award' => $my_award,
        'time' => time(),
        'status' => 0,
        '__user_table' => 'spring_lottery_award'
    );
    $award_result = $model -> insert($award_data,'lottery/lottery');
    $time = time();
    if($award_result){
        $the_arr = array('award' => $my_award,'telphone' => '','name' => '','status' => 0,'time' => $time);
        $redis -> sethash("award_{$imsi}:lottery_{$active_id}",array($award_result => $the_arr));
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
