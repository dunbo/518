<?php

include_once (dirname(realpath(__FILE__)).'/spring_init.php');

if ($imsi == '') {
    $tplObj -> display('spring_lottery_award.html');
    exit;
}

$option = array(
    'where' => array(
        'imsi' => $imsi,
        //'status' => array('exp'," = 1 or status = 2")
    ),
    'order' => 'time desc',
    'table' => 'spring_lottery_award'
);
$result = $model -> findAll($option,'lottery/lottery');
foreach($result as $key => $val){
    $val['award_level_name'] = $award_config[$val['award'] - 1][0];
    $val['award_name'] = $award_config[$val['award'] - 1][1];
    $val['award_type'] = $award_config[$val['award'] - 1][2];
    $val['the_time'] = date('Y-m-d H:i',$val['time']);
    $result[$key] = $val;
}

$tplObj -> out['result'] = $result;
$tplObj -> out['result_count'] = count($result);

$tplObj -> display('spring_lottery_award.html');
exit;