<?php
$active_id=346;

include_once (dirname(realpath(__FILE__)).'/../../init.php');
include_once 'flyback_fun.php';
$active_end_tm = "2015-11-18 23:59:59"; //活动结束日期
$now = time();
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
//没有session 跳转到首页
session_begin();

if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	echo 200;exit(0);
}


$lottery_num =  get_user_draw($redis,$model,$uid);

$tplObj -> out['lottery_num'] = $lottery_num;

//点击抽奖按钮日志
$log_data = array(
        'uid' => $_SESSION['USER_UID'],
        'users' =>$_SESSION['USER_NAME'],
        'imsi' => $_SESSION['USER_IMSI'],
        'device_id' => $_SESSION['DEVICEID'],
        'activity_id' => $active_id,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'sid' => $_POST['sid'],
        'time' => time(),
        'key' => 'lottery'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));

//抽奖操作
if(isset($_POST['type'])){
    $uid = $_SESSION['USER_UID'];
    if(empty($uid)){
        echo 100;exit(0);
    }
    if($lottery_num <= 0){
        // 没有抽奖机会
        echo 500;exit(0);
    }    
    //减少次数
    $redis -> set("flyback_num_uid_".$uid,0);
	load_helper('task');
	$task_client = get_task_client();
	$username =  $_SESSION['USER_NAME'];
	if($now < strtotime($active_end_tm)){
		if($_POST['type']=='draw'){
			$new_array = array();
			$new_array['uid'] =$uid;
			$new_array['username'] = $username;
			$new_array['user_game'] = $_POST['user_game'];
			$the_award = $task_client->do('flyback', json_encode($new_array));
			
			$lottery_rs = json_decode($the_award,true);
			$lottery_type = $lottery_rs['type'];
			$return_arr['lottery_type']=$lottery_type;
			$return_arr['pid']=$lottery_rs['pid'];
			if($lottery_type==1){//实物
				$return_arr['prizename']=$lottery_rs['name'];
				$award_name = $lottery_rs['name'];
				$return_arr['code'] =str_encode($uid.$_SESSION['USER_IMSI'].$lottery_rs['pid']);
				 //加密 $str = $uid.$_SESSION['USER_IMSI'].$pid
			}else if($lottery_type==2){//礼包
			    $award_name = '礼包';
				$gift_arr = json_decode($lottery_rs['gift_number'],true);
				$return_arr['softname']=$gift_arr['softname'];
				$return_arr['gift_number']=$gift_arr['gift_number'];
			}else if($lottery_type==-1){
				$return_arr['no_gift']=1;
			}
		}
		if($lottery_rs!=-1){
			$user_data = $redis->get('flyback_winer_info'.$uid);
			if(empty($lottery_rs['pid'])){
				$pid=8;
			}else{
				$pid = $lottery_rs['pid'];
			}
			
			$log_data = array(
					'uid'=>$uid,
					'imsi' => $_SESSION['USER_IMSI'],
					'device_id' => $_SESSION['DEVICEID'],
					'activity_id' => $active_id,
					'ip' => $_SERVER['REMOTE_ADDR'],
					'sid' => $_POST['sid'],
					'time' => time(),
					'award_level' => $pid,//pid
					'award_name' =>$award_name,
					'user' => $_SESSION['USER_NAME'],
					'name' => $user_data['name'],//姓名
					'telphone' => $user_data['mobile'],
					'address' => $user_data['address'],
					'package' => $gift_arr['package'],
					'gift' => $gift_arr['gift_number'],
					'key' => 'award'
			);
			permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
		}
		echo json_encode($return_arr);exit(0);
	}else{
		 // 没有抽奖机会
        echo 500;exit(0);
	}
	
	
}

exit;
