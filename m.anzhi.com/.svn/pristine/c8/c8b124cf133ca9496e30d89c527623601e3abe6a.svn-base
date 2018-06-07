<?php
include_once (dirname(realpath(__FILE__)).'/../../init.php');
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
$aid = $active_id = $_GET['aid'] ? $_GET['aid'] : $_POST['aid'];
if(ctype_digit($aid)==false){
    exit(0);
}
$sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
$time = time();

if($_GET['sid']){
    session_begin();
}else if($_POST['sid']){
    session_begin($_POST['sid']);
}

$version_code = $_SESSION['VERSION_CODE'];
$alone_update = $_SESSION['ALONE_UPDATE'];
$imei = $_SESSION['DEVICEID'];
$mac= $_SESSION['MAC'];
$uid = $_SESSION['USER_UID'];
$username=$_SESSION['USER_NAME'];
$imsi = $_SESSION['USER_IMSI'];


$mark = md5($mac.$imei);
$is_sign_key = 'gen_pre:sign:imei:'.$imei.':aid:'.$aid; //设备是否预约 只是活动这边使用 未与市场接口共用
$user_id_key = 'SUBSCRIBER:DATA:'."user_id:{$mark}"; //设备是否预约  和市场共用


$imsi_num = "general_lottery:{$uid}_num_{$aid}";
/*
$imsi_info = "general_lottery_imsi:info_{$aid}";
$share_imsi = "general_lottery:share_{$uid}_{$aid}";
$share_must = "general_lottery:share_must_{$aid}";
$imsi_package = "general_lottery:{$uid}_package_{$aid}";
*/

	$option = array(
		'where' => array(
			'id' => $active_id,
		),
		'table' => 'sj_activity',
		'field' => 'activity_page_id,activity_end_id,start_tm,end_tm',
	);
	$activity = $model->findOne($option);
        if($activity['end_tm'] <= time()){//已结束
            	$url = $center_url.$configs['activity_url']."lottery/appointment/end.php?aid=".$active_id."&sid=".$sid;
		header("Location: {$url}");
        }

        if($activity['start_tm'] >= time()){//未开始
            exit(0);
        }

function give_lottery_num(){
        global $redis;
        global $model;
        global $imsi_num;
        global $active_id;
        global $uid;
        global $username;
        global $imei;
        global $imsi;

        $now_num = $redis -> setx('incr',$imsi_num,1);
        $redis->expire($imsi_num,86400*90);
        $where = array(
                'aid' => $active_id,
                'uid' => $uid
        );
        $data = array(
                'lottery_num' => $now_num,
                'update_tm' => time(),
                '__user_table' => 'gm_lottery_num'
        );
        $result = $model -> update($where,$data,'lottery/lottery');
        if(!$result){
                $data = array(
                        'aid' => $active_id,
                        'uid' => $uid,
                        'username' => $username,
                        'imsi' => $imsi,
                        'imei' => $imei,
                        'lottery_num' => $now_num,
                        'update_tm' => time(),
                        '__user_table' => 'gm_lottery_num'
                );
                $result = $model -> insert($data,'lottery/lottery');
        }
}

function yuyue(){
        global $is_sign_key;
        global $redis;
        global $aid;
        global $uid;
        global $imei;
        global $model;
        global $mac;
        global $user_id_key;

        $notexist_t2 = $redis->setnx($is_sign_key,1);
        $redis->expire($is_sign_key,86400*90);
        if($notexist_t2===false){
            return;
        }

        $redis_soft = new GoRedisCacheAdapter(load_config('cache/soft_redis'));
        $pre_info = $redis_soft->gethash($user_id_key,$aid);

        if(!empty($pre_info)){//预约过了
            return;
        }


        $new_arr[$aid] = array('aid'=>$aid,'sid'=>'','s_time'=>time(),'cid'=>'');
        $redis_soft->sethash($user_id_key,$new_arr);


        //用户预约日志
        $log_data = array(
                        'uid'=>$uid,
                        'username' => $_SESSION['USER_NAME'],
                        'imsi' => $_SESSION['USER_IMSI'],
                        'device_id' => $_SESSION['DEVICEID'],
                        'time' => time(),
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
}

    function s_num_format($num)
	{
		$str_unit = '';
		if ($num <= 9999) {
			return $num;
		} elseif (10000<=$num&&$num<= 99999999) {
			$n = $num / 10000*10;
			$n = floor($n) / 10; 
			$str_unit = '万';		
		} elseif (100000000<=$num) {
			$n = $num / 100000000 * 10; 
			$n = floor($n) / 10;
			$str_unit = '亿';	
		}
		$n2 = substr($n,-1);
		if($n2 == 0){ $n = intval($n); }		
		return $n.$str_unit;		
	}
