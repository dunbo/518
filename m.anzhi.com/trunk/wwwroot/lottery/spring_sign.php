<?php

include_once (dirname(realpath(__FILE__)).'/spring_init.php');

if (!$imsi) {
    echo 0;
    exit;
}

// 今天的日期
$now_day = date('Ymd');

// 先获得可抽奖次数，防止并发问题
$exist_lottery_num = $redis->exists($rkey_imsi_lottery_num);
if (!$exist_lottery_num) {
    // 从库里读
    $option = array(
        'where' => array(
            'imsi' => $imsi
        ),
        'field' => 'lottery_num',
        'table' => 'spring_lottery_num',
    );
    $result = $model -> findOne($option,'lottery/lottery');
    if (!empty($result)) {
        $before_lottery_num = $result['lottery_num'];
    } else {
        $before_lottery_num = 0;
    }
    // 写缓存
    $redis->set($rkey_imsi_lottery_num, $before_lottery_num);
} else {
    $before_lottery_num = $redis->get($rkey_imsi_lottery_num);
}

// 尝试获得上次签到日期
$last_sign_day = $redis->gethash($rkey_imsi_sign_info, 'last_sign_day');
$sign_sum = $redis->gethash($rkey_imsi_sign_info, 'sign_sum');
if (empty($last_sign_day) || empty($sign_sum)) {
    // 缓存没读到，从库里取
    $option = array(
        'where' => array(
            'imsi' => $imsi
        ),
        'field' => 'last_sign_day,sign_sum',
        //'cache_time' => 600,
        'table' => 'spring_sign_info',
    );
    $result = $model -> findOne($option,'lottery/lottery');
    if (!empty($result)) {
        $last_sign_day = $result['last_sign_day'];
        $sign_sum = $result['sign_sum'];
    } else {
        $last_sign_day = '';
        $sign_sum = 0;
    }
    // 写缓存
    $redis->hset($rkey_imsi_sign_info, 'last_sign_day', $last_sign_day);
    $redis->hset($rkey_imsi_sign_info, 'sign_sum', $sign_sum);
}

if ($last_sign_day == $now_day) {
    echo $sign_sum;
    exit;
}

// 可以签到
$sign_sum++;
if ($sign_sum == 1) {
    // 第一次签到
    $new_data = array(
        'imsi' => $imsi,
        'last_sign_day' => $now_day,
        'sign_sum' => $sign_sum,
        'time' => time(),
        '__user_table' => 'spring_sign_info'
    );
    $model -> insert($new_data,'lottery/lottery');
} else {
    // update
    $sum_where = array('imsi' => $imsi);
    $sum_data = array(
        'last_sign_day' => $now_day,
        'sign_sum' => $sign_sum,
        'time' => time(),
        '__user_table' => 'spring_sign_info'
    );
    $model -> update($sum_where,$sum_data,'lottery/lottery');
}

// 写缓存
$redis->hset($rkey_imsi_sign_info, 'last_sign_day', $now_day);
$redis->hset($rkey_imsi_sign_info, 'sign_sum', $sign_sum);

// 如果次数是5的倍数，则赠送抽奖次数
if ($sign_sum > 0 && $sign_sum % 5 == 0) {
    $encourage_num = ($sign_sum/5) + 1;
    // 更新缓存
    $now_num = $before_lottery_num + $encourage_num;
    $redis->set($rkey_imsi_lottery_num, $now_num);
    
    // 可抽奖次数写数据库
    // 查找此imsi是否存在
    $option = array(
        'where' => array(
            'imsi' => $imsi
        ),
        'field' => 'id',
        //'cache_time' => 600,
        'table' => 'spring_lottery_num',
    );
    $result = $model -> findOne($option,'lottery/lottery');
    if (empty($result)) {
        // insert
        $new_data = array(
			'imsi' => $imsi,
			'lottery_num' => $now_num,
			'time' => time(),
			'__user_table' => 'spring_lottery_num'
		);
		$model -> insert($new_data,'lottery/lottery');
    } else {
        // update
        $num_where = array('imsi' => $imsi);
        $num_data = array(
            'lottery_num' => $now_num,
            'time' => time(),
            '__user_table' => 'spring_lottery_num'
        );
        $model -> update($num_where,$num_data,'lottery/lottery');
    }
}

if (!$sign_sum) {
    $sign_sum = 0;
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'sign_sum' => $sign_sum,
    'time' => time(),
    'key' => 'spring_sign'
);
permanentlog($activity_log_file, json_encode($log_data));

echo $sign_sum;
exit;