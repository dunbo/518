<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$model = new GoModel();
session_begin();
$aid = $_GET['aid'];
$package = $_GET['package'];
$imsi = $_SESSION['USER_IMSI'];
$uid = $_SESSION['USER_UID'];
$imei = $_SESSION['DEVICEID'];


$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $aid,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'sid' => $_GET['sid'],
	'time' => time(),
	'package' => $package,
        'users'=> '',
        'uid'=> $uid,
	'key' => 'download_soft'
);

permanentlog('activity_'.$aid.'.log', json_encode($log_data));

if(empty($uid)){
    echo 1;exit(0);
}

//砸蛋次数处理

$package_imei_key = 'yd:imei:'.$imei.':package:'.$package;//已下载包名设备

$user_today_getnum_key = 'yd:getnum:uid:'.$uid.':date:'.date('Ymd');
$get_num = $redis->get($user_today_getnum_key);
if($get_num>=3){
    echo 2;exit(0);
}

$user_today_shangxian_key = 'yd:shangxian:uid:'.$uid.':date:'.date('Ymd');

$res_imei = $redis->get($package_imei_key);
if(!empty($imei)&&empty($res_imei)){ //没下过

                $notexist_t2 = $redis->setnx($user_today_shangxian_key,1);
                $redis->expire($user_today_shangxian_key,86400);
                if($notexist_t2===false){//今天做过了
                    echo 2;exit(0);
                }

                $option = array(
                        'where' => array(
                                'uid' => $uid,
                        ),
                        'table' => 'xy2_draw_userinfo',
                        'field' => 'turncard_num',
                );

                $ret= $model->findOne($option,'lottery/lottery');
                if($ret['turncard_num']>=9){
                    echo 1;exit(0);
                }

                $data = array(
                        'turncard_num' => array('exp','`turncard_num`+1'),
                        'turncard_num_sum' => array('exp','`turncard_num_sum`+1'),
                        'update_tm' => time(),
                        '__user_table' => 'xy2_draw_userinfo'
                );
                $where = array(
                        'uid' => $uid
                );
                $model->update($where, $data,'lottery/lottery');
                $redis->setx('incr','yd:sign:lottery_num:uid:'.$uid,1);
                $redis->setx('incr',$user_today_getnum_key,1);

                $redis->set($package_imei_key,1,86400*10);
}
