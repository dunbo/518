<?php

include_once (dirname(realpath(__FILE__)).'/../../init.php');

$config = load_config('lottery_cache/redis','lottery');
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}

$redis_soft = new GoRedisCacheAdapter(load_config('cache/soft_redis'));

$model = new GoModel();
$imsi = $_SESSION['USER_IMSI'];
$aid = $_POST['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}

session_begin($_POST['sid']);
$uid = $_SESSION['USER_UID'];
$imei = $_SESSION['DEVICEID'];
$mac = $_SESSION['MAC'];
$imsi_num = "general_lottery:{$uid}_num_{$aid}";
$mark = md5($mac.$imei);

//设备预约缓存

            $is_sign_key = 'gen_pre:sign:imei:'.$imei.':aid:'.$aid.':mark:'.$mark;
            $notexist_t2 = $redis->setnx($is_sign_key,1);
            $redis->expire($is_sign_key,86400*90);
            if($notexist_t2===false){
                echo 2;exit(0);
            }

$user_id_key = 'SUBSCRIBER:DATA:'."user_id:{$mark}";

$pre_info = $redis_soft->gethash($user_id_key,$aid);

if(!empty($pre_info)){//预约过了
    echo 2;exit(0);
}

$new_arr[$aid] = array('aid'=>$aid,'sid'=>'','s_time'=>$time,'cid'=>'');

$redis_soft->sethash($user_id_key,$new_arr);
$redis_soft->expire($user_id_key,86400*90);


//用户预约日志
brush_second_do($aid);
/*
$time=time();
$yuyue_key_today = 'gen_pre_brush:yuyue:uid:'.$uid.':aid:'.$aid.':'.date('Ymd',time());
$redis->setnx($yuyue_key_today,$time);
$redis->expire($yuyue_key_today,86400);
*/

$log_data = array(
                'uid'=>$uid,
                'username' => $_SESSION['USER_NAME'],
                'imsi' => $_SESSION['USER_IMSI'],
                'device_id' => $_SESSION['DEVICEID'],
                'time' => $time,
                'activity_id' => $aid,
                'key' => 'reserve'
);
permanentlog('activity_'.$aid.'.log', json_encode($log_data));

//总次数处理

$activity_option = array(
	'where' => array(
		'id' => $aid
	),
	'cache_time' => 300,
	'table' => 'sj_activity'
);
$activity_result = $model -> findOne($activity_option);

$page_option = array(
	'where' => array(
		'ap_id' => $activity_result['activity_page_id']
	),
	'cache_time' => 300,
	'table' => 'sj_activity_page'
);
$page_result = $model -> findOne($page_option);

$add_num = rand($page_result['no_prize_pic'],$page_result['no_prize_text']);
$where = array(
        'ap_id' => $page_result['ap_id']
);
$data = array(
        'lottery_num_limit' => $page_result['lottery_num_limit']+$add_num,
        '__user_table' => 'sj_activity_page'
);
$result = $model -> update($where,$data);

//入库 preusers

				$data = array(
					'imei' => $imei,
					'mac' => $mac,
					'uid' => $uid,
                                        'uid_2' => $_SESSION['USER_ID'],
					'create_tm' => time(),
					'update_tm' => time(),
					'aid' => $aid,
					'__user_table' => 'pre_users'
				);
				$result = $model -> insert($data,'lottery/lottery');

echo 1;exit(0);
