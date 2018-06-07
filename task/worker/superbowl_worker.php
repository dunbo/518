<?php

include dirname(__FILE__).'/../init.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();

$activity_option = array(
    'where' => array(
        'config_type' => 'SUPERBOWL_LOTTERY_AWARD',
        'status' => 1
    ),
    'cache_time' => 86400,
    'table' => 'pu_config'
);
$result = $model -> findOne($activity_option);
$activity_config = json_decode($result['configcontent'],true);

$active_id = $activity_config['activity_id'];//活动id
$award_config = $activity_config['award_config'];

//var_dump($award_config);

// 奖品等级个数
$award_level_count = count($award_config);

// 测试中奖概率

$each_day_award = array(
	'20151223' => array(0,0,0,1,1,3,5),
	'20151224' => array(0,0,1,0,1,2,3),
	'20151225' => array(1,0,0,0,1,3,5),
	'20151226' => array(0,0,0,0,1,1,3),
	'20151227' => array(0,0,0,0,1,1,3),
	'20151228' => array(0,1,1,0,1,1,3),
    '20151229' => array(0,0,0,1,1,1,2),
    '20151230' => array(0,0,0,0,1,1,2),
    '20151231' => array(0,0,0,0,1,1,2),
    '20160101' => array(0,0,0,0,1,1,2),
    '20160102' => array(0,0,0,0,1,1,1)
);

// 线上中奖概率
//$each_day_award = array(
//    '20151230' => array(0,0,0,0,1,2,2),
//    '20151231' => array(0,0,0,1,1,3,5),
//    '20160101' => array(0,0,1,0,1,2,3),
//    '20160102' => array(1,0,0,0,1,3,5),
//    '20160103' => array(0,1,0,0,1,3,5),
//    '20160104' => array(0,0,0,0,1,1,3),
//    '20160105' => array(0,0,0,0,1,1,3),
//    '20160106' => array(0,1,1,0,1,1,3),
//    '20160107' => array(0,0,0,1,1,1,2),
//    '20160108' => array(0,0,0,0,1,1,2),
//    '20160109' => array(0,0,0,0,1,1,2),
//    '20160110' => array(0,0,0,0,1,1,1)
//);

// 算出下一天累计奖品总数
$the_time = array();
$mount_award_num = array();
//$award_level_count = 7
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

$sum = 1800; //评估每日参加活动人数
$r_cache_time = '5184000';//redis缓存时间为两个月

ini_set('default_socket_timeout', -1);
load_helper('task');
$worker = get_task_worker();
$worker->addFunction("superbowl", "get_num");
while ($worker->work());

/*
** 返回中奖信息数据，一等奖：award_level=1，依次类推
*/

function get_num($jobs) {
    global $redis;
    global $model;
    global $active_id;
    global $the_time;
    global $award_level_count;//中奖等级数量
    global $award_config;
    global $sum;
    global $r_cache_time;


    $now = date('Ymd');//当天日期
    $imsi = $jobs->workload();

    $return_arr = array();

    if (empty($imsi)) {
        $return_arr['award_level']= $award_level_count + 1;
        return json_encode($return_arr);;
    }

    // 截止今天剩余奖品个数缓存
    $rkey_today_award_left = "superbowl:{$active_id}:{$now}:award_left";
    $today_award_left = $redis->gethash($rkey_today_award_left);
    if (empty($today_award_left)) {
        // 实时统计入缓存
        $option = array(
            'field' => 'award, count(*) as count',
            'group' => 'award',
            'table' => 'superbowl_winner',
        );
        $list = $model->findAll($option, 'lottery/lottery');
        $already_use = array();
        foreach ($list as $row) {
            $award = $row['award'];
            $count = $row['count'];
            $already_use[$award - 1] = $count;
        }
        $today_award_left = array();
        foreach ($the_time[$now] as $key => $value) {
            $left = $value - $already_use[$key];
            if ($left >= 0) {
                $today_award_left[$key] = $left;
            } else {
                $today_award_left[$key] = 0;
            }
        }
        $redis->sethash($rkey_today_award_left, $today_award_left, $r_cache_time);
    }
    $award_level = lottery($today_award_left, $sum);
    $award_level++;//1=>一等奖，。。。7=>7等奖
    if ($award_level > $award_level_count) {
        $return_arr['award_level']= $award_level_count + 1;
        return json_encode($return_arr);
    }
    // 中奖了
    // 更新缓存
    $today_award_left[$award_level - 1] = $today_award_left[$award_level - 1] - 1;
    $redis->sethash($rkey_today_award_left, $today_award_left, $r_cache_time);
    // 扫数据库，查看此奖项是不是真有剩（防止缓存出错）
    $option = array(
        'where' => array(
            'award' => $award_level,
        ),
        'field' => 'award, count(*) as count',
        'group' => 'award',
        'table' => 'superbowl_winner',
    );
    $list = $model->findOne($option, 'lottery/lottery');
    $award_used_count = empty($list['count']) ? 0 : $list['count'];
    if ($award_used_count >= $the_time[$now][$award_level - 1]) {
        // 真没奖了，一般不会走到这的
        $return_arr['award_level']= $award_level_count + 1;
        return json_encode($return_arr);
    }

    $data = array(
        'imsi' => $imsi,
        'award' => $award_level,
        'add_tm' => time(),
        'status' => 0,
        '__user_table' => 'superbowl_winner',
    );
    $model->insert($data, 'lottery/lottery');
    $return_arr['award_level']= $award_level;
    $return_arr['package'] = $package;
    $return_arr['gift_card_no']= $card_no;
    $return_arr['gift_card_pwd']= $card_pwd;
    return json_encode($return_arr);

}

function lottery($gift_base, $sum) {
    $gift_line = array();
    $nows = 0;
    if(!$gift_base){
        return count($gift_base);
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