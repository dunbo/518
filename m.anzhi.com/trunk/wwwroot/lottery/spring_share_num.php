<?php

/*
** 用户分享，更新缓存，记录日志
*/

include_once (dirname(realpath(__FILE__)).'/spring_init.php');

if ($imsi == '') {
    echo 0;
    exit;
}

// 今天的日期
$now_day = date('Ymd');

// 判断用户今天是否分享过
$share_flag = 1;
$last_share_day = $redis->gethash($rkey_imsi_ever_share, 'last_share_day');
if (empty($last_share_day)) {
    // 没有分享过
    $share_flag = 0;
} else {
    if ($last_share_day == $now_day) {
        // 分享过
        $share_flag = 1;
    } else {
        // 没有分享过
        $share_flag = 0;
    }
}

if (!$share_flag) {
    // 记录一下今天已分享过
    $redis->hset($rkey_imsi_ever_share, 'last_share_day', $now_day);
    $share_num = $redis->hset($rkey_imsi_ever_share, 'share_num', 1);
    // 可抽奖次数加1
    $now_num = $redis->setx('incr', $rkey_imsi_lottery_num, 1);
    // 查一下此imsi是否存在于表spring_lottery_num中
    $option = array(
        'where' => array(
            'imsi' => $imsi
        ),
        'table' => 'spring_lottery_num'
    );
    $find = $model -> findOne($option,'lottery/lottery');
    if (!$find) {
        //insert
        $data = array(
            'imsi' => $imsi,
            'lottery_num' => $now_num,
            'time' => time(),
            '__user_table' => 'spring_lottery_num'
        );
        $model -> insert($data,'lottery/lottery');
    } else {
        //update
        $where = array(
            'imsi' => $imsi
        );
        $data = array(
            'lottery_num' => $now_num,
            'time' => time(),
            '__user_table' => 'spring_lottery_num'
        );
        $model->update($where, $data, 'lottery/lottery');
    }
} else {
    $share_num = $redis->setx('HINCRBY', $rkey_imsi_ever_share, 'share_num', 1);
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
    'key' => 'spring_share_num'
);
permanentlog($activity_log_file, json_encode($log_data));

echo $share_num;
exit;