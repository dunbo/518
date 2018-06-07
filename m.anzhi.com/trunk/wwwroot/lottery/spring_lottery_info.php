<?php

include_once (dirname(realpath(__FILE__)).'/spring_init.php');

if ($imsi == '') {
    exit;
}

// 可用抽奖次数
$my_num = $redis -> setx('incr', $rkey_imsi_lottery_num,0);
if($my_num === false){
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

// 查找此用户中的哪个奖，然后决定需要填写的信息
$option = array(
    'where' => array(
        'imsi' => $imsi,
        'telephone' => '',
    ),
    'table' => 'spring_lottery_award'
);
$result = $model -> findOne($option,'lottery/lottery');
if (empty($result)) {
    exit;
}
$award_level = $result['award'];

// 判断中奖的内容是否为实物
$award_type = $award_config[$award_level-1][2];

// 几等奖
$award = $award_config[$award_level-1][0];
// 奖品内容
$award_name = $award_config[$award_level-1][1];

$tplObj->out['award_type'] = $award_type;
$tplObj->out['award'] = $award;
$tplObj->out['award_name'] = $award_name;
$tplObj -> display('spring_lottery_info.html');
exit;