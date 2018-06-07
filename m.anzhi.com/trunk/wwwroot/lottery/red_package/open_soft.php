<?php
include_once ('./fun.php');
//$active_id = isset($_GET['aid'])?$_GET['aid']:183;
/*
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
session_begin();
*/
$softid = $_GET['softid'];
$package = $_GET['package'];
$imsi = $_SESSION['USER_IMSI'];

$telphone = $_GET['telphone'];

$az_user = $_GET['az_user'];
$server_name = $_GET['server_name'];
$play_name = $_GET['play_name'];


//增加次数
if(!empty($uid)){
    $tmp = $redis->gethash($p_key);

    if($tmp[$package]==1){
        $redis->hdel($p_key,$package);
        $redis->sethash($p_done_key,array($package => 1));
        $redis->expire($p_done_key,86400*90);


	$down_num = $redis -> setx('incr',$down_num_key,1);//统计增加次数 如果已经达到 则不增加
        $redis->expire($down_num,86400*90);
        $down_set_num = $redis->get($down_set_num);
        $down_set_num = (int)$down_set_num;
        if($down_num>$down_set_num){
			$down_num = $redis -> setx('incr',$down_num_key,-1);
        }else{
			$now_num = $redis -> setx('incr',$lottery_num_key,1);
                        $redis->expire($imsi_num,86400*90);
			$where = array(
				'aid' => $active_id,
				'uid' => $uid
			);
			$data = array(
				'lottery_num' => $now_num,
				'update_tm' => time(),
				'__user_table' => 'red_package_lottery_num'
			);
			$result = $model -> update($where,$data,'lottery/lottery');
			if(!$result)
			{
				$data = array(
					'aid' => $active_id,
					'uid' => $uid,
					'username' => $username,
					'imsi' => $imsi,
					'imei' => $imei,
                                        'mac' => $_SESSION['MAC'],
					'lottery_num' => $now_num,
					'update_tm' => time(),
					'__user_table' => 'red_package_lottery_num'
				);
				$result = $model -> insert($data,'lottery/lottery');
                        }
        }
    }
}

$log_data = array(
	'imsi' => $imsi,
	'device_id' => $_SESSION['DEVICEID'],
	'activity_id' => $active_id,
	'ip' => $_SERVER['REMOTE_ADDR'],
	'package' => $package,
	'sid' => $_GET['sid'],
	'time' => time(),
	'key' => 'open_soft'
);
if(!empty($telphone)){
    $log_data['telphone'] =$telphone;
}

if(!empty($az_user)){
    $log_data['az_user'] =$az_user;
    $log_data['server_name'] =$server_name;
    $log_data['play_name'] =$play_name;
}


permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
return 200;
