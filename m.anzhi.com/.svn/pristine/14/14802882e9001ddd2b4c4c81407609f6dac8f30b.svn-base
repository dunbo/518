<?php

include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis','lottery');

$model = new GoModel();
session_begin();
$imsi = $_SESSION['USER_IMSI'];
$aid = $_POST['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
$uid = $_SESSION['USER_UID'];
$imei = $_SESSION['DEVICEID'];
$model = new GoModel();

if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}


if($_POST){


            $g_id = $_POST['g_id'];
            $is_real_get_gift= 'gen_pre:realgetgift:uid:'.$uid.':aid:'.$aid.':'.$g_id;
            $is_give_gift_key = 'gen_pre:gift:uid:'.$uid.':aid:'.$aid.':'.$g_id;
            $notexist_t3 = $redis->setnx($is_give_gift_key,1);
            $redis->expire($is_give_key,86400*90);
            if($notexist_t3===false){
            //if(false){
                echo 2;exit(0); //已领取过了
            }else{
                    $gift_number = $redis -> rpop("bespoke_gift:virtual_{$g_id}");
                    $gift_number = json_decode($gift_number,true);
                    if(empty($gift_number)){
                        echo 3;exit(0);//礼包已经领取完
                    }
                    $where = array(
                        'first_text' =>$gift_number['first_text'], //redis里来的
                        'pid' =>$g_id,
                    );
                    $data = array(
                        'imsi' => $imsi,
                        'uid' => $uid,
                        'status' => 1,
                        'update_tm' => time(),
                        '__user_table' => 'gm_bespoke_gift_code'
                    );
                    $model -> update($where,$data,'lottery/lottery');

                    //用户领取礼包
                    $log_data = array(
                                    'gift'=>$gift_number['first_text'],
                                    'gid'=>$g_id,
                                    'uid'=>$uid,
                                    'username' => $_SESSION['USER_NAME'],
                                    'imsi' => $_SESSION['USER_IMSI'],
                                    'device_id' => $_SESSION['DEVICEID'],
                                    'time' => time(),
                                    'activity_id' => $aid,
                                    'key' => 'receive_gift'
                    );
                    $redis->set($is_real_get_gift,1,86400*30);
                    permanentlog('activity_'.$aid.'.log', json_encode($log_data));


            }
    echo 1;exit(0);
}
