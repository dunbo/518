<?php
include_once (dirname(realpath(__FILE__)).'/../init.php');
include_once 'rebate_draw_function.php';
$config = load_config('lottery_cache/redis',"lottery");
if ($config) {
	$redis = new GoRedisCacheAdapter($config);
} else {
	$redis = GoCache::getCacheAdapter('redis');
}
$model = new GoModel();
//没有session 跳转到首页

$b = array("460042359383380","460086504303738","460011705796755","460066600163811","460073760208920","460092178990235","460034351743375","460086530305114","460024872225457","460071144031826","460030233084908","460088584916656","460013804672458","460013855218561","460039294304493","460073258131998","460070875446228","460059990915940","460015370965804","460094962560934","460092343491874","460090393188926","460086690816967","460053412941116","460039791800077","460059800300104","460086622319091","460087246883404","460078192938724","460078140494581","460019626873075","460058071021740","460034240615977","460025858003948","460099604764641","460043462001704","460073659513597","460043237969651","460046116390344","460070159321734","460055352379735","460097129613822","460016603109971","460013098810431","460040402766596","460094160059713","460082125214678","460066191885583","460065071217878","460059791540009");

$imsi_key = array_rand($b);
$uid = $b[$imsi_key];
$_POST['type'] = 'draw';

$active_id=223;

//点击抽奖按钮日志
$log_data = array(
        'uid' => $uid,
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

	
	load_helper('task');
	$task_client = get_task_client();
	$username =  $_SESSION['USER_NAME'];
	if($_POST['type']=='draw'){
			$new_array = array();
			$new_array['uid'] =$uid;
			$new_array['username'] = $username;
			$new_array['user_game'] = $_POST['user_game'];
			$the_award = $task_client->do('rebate_draw', json_encode($new_array));			
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
				if(empty($gift_arr['gift_number'])){
					$return_arr['no_gift']=1;
				}else{
					$return_arr['no_gift']=2;
				}
			}else if($lottery_type==-1){
				$return_arr['no_gift']=1;
			}
	}
	if($lottery_rs!=-1){
		$user_data = $redis->get('rebate_draw_userinfo'.$uid);
		$user_data = json_decode($user_data,true);
		if(empty($lottery_rs['pid'])){
			$pid=5;
		}else{
			$pid = $lottery_rs['pid'];
		}
		
		$log_data = array(
				'uid'=>$uid,
				'imsi' => $_SESSION['USER_IMSI'],
				'device_id' => $_SESSION['DEVICEID'],
				'activity_id' => $active_id,
				'ip' => $_SERVER['REMOTE_ADDR'],
				'sid' => $_GET['sid'],
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
	$return_arr['now_num'] = get_uid_draw_num($redis,$model,$uid);	
	echo json_encode($return_arr);exit(0);


exit;
