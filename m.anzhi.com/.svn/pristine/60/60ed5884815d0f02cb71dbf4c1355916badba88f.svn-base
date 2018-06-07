<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

//过期直接返回失败
if(time()>strtotime('2016-01-05 22:00:00')){
    echo -1;exit(0);
}



$model = new GoModel();
session_begin($_POST['sid']);
$aid = $_POST['aid'];
$uid = $_SESSION['USER_UID'];

if(empty($uid)){
    echo 2;exit(0);
}

            $is_sign_key = 'yd:sign:uid:'.$uid.':date:'.date('Ymd');
            //$notexist_t2 = $redis->setnx($is_sign_key,date('Ymd H:i:s',time()));
            $notexist_t2 = $redis->setnx($is_sign_key,1);
            $redis->expire($is_sign_key,86400);
            if($notexist_t2===false){//今天做过了
                echo 2;exit(0);
            }

$sign_day=date('Ymd')-20160101+1;


//用户签到日志
$log_data = array(
	'uid'=>$uid,
	'username' => $_SESSION['USER_NAME'],
	'imsi' => $_SESSION['USER_IMSI'],
	'device_id' => $_SESSION['DEVICEID'],
	'time' => time(),
	'activity_id' => $aid,
	'key' => 'sign'
);

permanentlog('activity_'.$aid.'.log', json_encode($log_data));

$option = array(
	'where' => array(
		'uid' => $uid,
	),
	'table' => 'xy2_draw_userinfo',
	'field' => 'turncard_num_sum,turncard_num,sign_str',
);


$sign = $model->findOne($option,'lottery/lottery');
$sign_str = $sign['sign_str'].','.$sign_day;

if($sign['turncard_num_sum']>=5){
    $redis->set('yd:sign_str:uid:'.$uid,$sign_str,86400*6);
    echo 1;exit(0);
}

$data = array(
        'turncard_num' => array('exp','`turncard_num`+1'),
        'turncard_num_sum' => array('exp','`turncard_num_sum`+1'),
        'sign_str' => $sign_str,
        'update_tm' => time(),
        '__user_table' => 'xy2_draw_userinfo'
);
$where = array(
        'uid' => $uid
);
$res = $model->update($where, $data,'lottery/lottery');
if($res!=false){
    $redis->set('yd:sign_str:uid:'.$uid,$sign_str,86400*6);
}

$redis->setx('incr','yd:sign:lottery_num:uid:'.$uid,1);

echo 1;exit(0);
