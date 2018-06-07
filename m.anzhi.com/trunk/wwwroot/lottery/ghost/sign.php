<?php
include_once ('./fun.php');
session_begin($sid);
$url = "http://promotion.anzhi.com/lottery/ghost/index.php?aid={$active_id}&sid={$sid}";
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}
$res = $redis->get("ghost:{$active_id}_is_sign:".$uid);

if($res==1){
    echo 1;exit(0);
}

//$tm_con = get_config($active_id,$uid);
$time = time();
//用户预约日志
$log_data = array(
	'uid'=>$uid,
	'username' => $_SESSION['USER_NAME'],
	'imsi' => $_SESSION['USER_IMSI'],
	'device_id' => $_SESSION['DEVICEID'],
	'time' => $time,
	'activity_id' => $active_id,
	'key' => 'sign'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	


$redis->set("ghost:{$active_id}_is_sign:".$uid,1,86400*30);

$data = array(
	'draw_data_num' => 1,
	'uid' => $uid,
	'aid' => $active_id,
);
add_user($data,$time);
exit(json_encode(array('code'=>1,'msg'=>'成功')));
