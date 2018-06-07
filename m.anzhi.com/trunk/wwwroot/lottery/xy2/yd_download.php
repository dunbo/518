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

//$package_key = 'yd:uid:'.$uid.':package:'.$package;//已下载包名
$user_today_downnum_key = 'yd:downnum:uid:'.$uid.':date:'.date('Ymd');

$user_today_shangxian_key = 'yd:shangxian:uid:'.$uid.':date:'.date('Ymd');

$user_downsum_key = 'yd:downsum:uid:'.$uid;

$gold_user_lotterynum_key = 'yd:gold:lotterynum:uid:'.$uid;//金蛋次数
$silver_user_lotterynum_key = 'yd:silver:lotterynum:uid:'.$uid;//银蛋次数
//$res = $redis->get($package_key);
$res_imei = $redis->get($package_imei_key);
if(!empty($imei)&&empty($res_imei)){ //没下过

    $is_shangxian = $redis->get($user_today_shangxian_key);
    if(empty($is_shangxian)){
        $now_num = $redis->setx('incr',$user_today_downnum_key,1);
        if($now_num<=2){

                $now_sum = $redis->setx('incr',$user_downsum_key,1);
                $option = array(
                        'where' => array(
                                'uid' => $uid,
                        ),
                        'table' => 'xy2_draw_userinfo',
                        'field' => 'gold_num,silver_num',
                );


                $ret= $model->findOne($option,'lottery/lottery');
                if($ret['gold_num']>=3||$ret['silver_num']>=10){
                    echo 1;exit(0);
                }

                if($now_num==2){
                    $redis->set($user_today_shangxian_key,1,86400);
                }

                if($now_sum<3){
                        $data = array(
                                'silver_num' => array('exp','`silver_num`+1'),
                                'silver_sum' => array('exp','`silver_sum`+1'),
                                'update_tm' => time(),
                                '__user_table' => 'xy2_draw_userinfo'
                        );
                        $where = array(
                                'uid' => $uid
                        );
                        $model->update($where, $data,'lottery/lottery');
                        $redis->setx('incr',$silver_user_lotterynum_key,1);

                }else if($now_sum==3){
                    $data = array(
                            'gold_num' => array('exp','`gold_num`+1'),
                            'gold_sum' => array('exp','`gold_sum`+1'),
                            'update_tm' => time(),
                            '__user_table' => 'xy2_draw_userinfo'
                    );
                    $where = array(
                            'uid' => $uid
                    );
                    $model->update($where, $data,'lottery/lottery');    
                    $redis->setx('incr',$gold_user_lotterynum_key,1);

                    $data = array(
                            'silver_num' => array('exp','`silver_num`+1'),
                            'silver_sum' => array('exp','`silver_sum`+1'),
                            'update_tm' => time(),
                            '__user_table' => 'xy2_draw_userinfo'
                    );
                    $where = array(
                            'uid' => $uid
                    );
                    $model->update($where, $data,'lottery/lottery');
                    $redis->setx('incr',$silver_user_lotterynum_key,1);
                    $redis->set($user_downsum_key,0);
                }
        }
    }
    //$redis->set($package_key,1,86400*10);
    $redis->set($package_imei_key,1,86400*10);
}
