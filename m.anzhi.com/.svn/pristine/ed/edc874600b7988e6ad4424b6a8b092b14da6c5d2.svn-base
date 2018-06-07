<?php
include_once (dirname(realpath(__FILE__)).'/spring_init.php');

if ($imsi == '') {
    $tplObj -> display('spring_lottery.html');
    exit;
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_GET['sid'],
    'time' => time(),
    'key' => 'spring_lottery'
);

permanentlog($activity_log_file, json_encode($log_data));

// 可用抽奖次数
$my_num = $redis -> get($rkey_imsi_lottery_num);
if($my_num === null){
    $num_option = array(
        'where' => array(
            'imsi' => $imsi,
        ),
        'table' => 'spring_lottery_num'
    );
    $num_result = $model -> findOne($num_option,'lottery/lottery');
    if (!empty($num_result)) {
        $my_num = $num_result['lottery_num'];
    } else {
        $my_num = 0;
    }
    $my_num = $redis -> setx('incr',$rkey_imsi_lottery_num, $my_num);
}
$tplObj -> out['my_num'] = $my_num;

$get_award_status = 200;
// 检查是否中过奖且未填信息
$option = array(
    'where' => array(
        'imsi' => $imsi,
        'telephone' => '',
    ),
    'table' => 'spring_lottery_award'
);
$result = $model -> findOne($option,'lottery/lottery');
if (!empty($result)) {
    // 跳转到填写信息页面去
    header("location:/lottery/spring_lottery_info.php?sid={$_GET['sid']}");
}

$tplObj->out['get_award_status'] = $get_award_status;// 是否已填中奖信息
$tplObj -> display('spring_lottery.html');

