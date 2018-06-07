<?php
/*
** 超级碗填写获奖信息
*/

include_once (dirname(realpath(__FILE__)).'/../../init.php');
include_once (dirname(realpath(__FILE__)).'/superbowl_fun.php');
$model = new GoModel();
$active_info  = getActiveConfig();
$activity_config = json_decode($active_info['configcontent'],true);
$active_id = $activity_config['activity_id'];
// 活动日志
$activity_log_file = "activity_{$active_id}.log";

$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
    $redis = new GoRedisCacheAdapter($config);
} else {
    $redis = GoCache::getCacheAdapter('redis');
}

// 加载session，获得用户相关信息
session_begin();

$sid = $_GET['sid'];
if($_SESSION['USER_IMSI']){
    $imsi = $_SESSION['USER_IMSI'];
}
//var_dump($imsi);
$imsi_status = 0;

//无sim卡
if(!$imsi || $imsi == '000000000000000'){
    $imsi = '';
} else {
    $imsi_status = 1;
}


if ($imsi_status != 1) {
    echo -1;
    exit;
}

$name = trim($_POST['name']);
$telephone = trim($_POST['telephone']);

if (empty($name)) {
    echo 500;
    exit;
}

if (empty($telephone)) {
    echo 501;
    exit;
}

// 检查名字长度，不要超过10个字
if (mb_strlen($name, 'utf-8') > 10) {
    echo 502;//名字太长
    exit;
}

if(!preg_match("/^1[34578][0-9]{9}$/",$telephone) || strlen($telephone) != 11){
    echo 503;//电话号码不对
    exit;
}

// 查找此用户中的哪个奖，然后决定需要填写的信息
$option = array(
    'where' => array(
        'imsi' => $imsi,
        'status' =>0
    ),
    'order' =>'add_tm desc',
    'table' => 'superbowl_winner'
);
$result = $model -> findOne($option,'lottery/lottery');
if (empty($result)) {
    echo 400;// 没有未填的中奖信息
    exit;
}

$id = $result['id'];
$award = $result['award'];
$where = array(
   'id' =>$id
);

$data = array(
    'name' => $name,
    'telephone' => $telephone,
    'up_tm' => time(),
    'status' => 1,
    '__user_table' => 'superbowl_winner'
);

$u_result = $model -> update($where,$data,'lottery/lottery');
if (empty($u_result)) {
    echo 300;
    exit;
}

// 记日志
$log_data = array(
    'imsi' => $imsi,
    'device_id' => $_SESSION['DEVICEID'],
    'activity_id' => $active_id,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'sid' => $_POST['sid'],
    'award_level' => $award,
    'telphone' => $telephone,
    'name' => $name,
    'time' => time(),
    'key' => 'award'
);
permanentlog($activity_log_file, json_encode($log_data));
$superbowl_award = $redis -> gethash("superbowl_award_{$active_id}_{$imsi}");
$award_num = count($superbowl_award);
if($award_num>0){
    $key = $award_num+1;
}else{
    $key = 0;
}
$r_data[$key] = array(
    'id'=>$id,
    'imsi'=>$imsi,
    'award'=>$award,
    'add_tm'=>date('Y-m-d',$result['add_tm']),
    'up_tm'=>time(),
    'telephone'=>$telephone,
    'name'=>$name,
    'status'=>1
);
//permanentlog($activity_log_file, print_r($r_data,true));
$redis -> sethash("superbowl_award_{$active_id}_{$imsi}",$r_data,86400*15);
echo 200;
exit;