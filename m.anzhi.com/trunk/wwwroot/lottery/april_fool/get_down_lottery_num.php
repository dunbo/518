<?php
include_once ('./fun.php');
session_begin($sid);
if(isset($_SESSION['USER_UID'])){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = "http://promotion.anzhi.com/lottery/ranking/index.php?aid={$active_id}&sid={$sid}";
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}
if($_POST){
	$day = date('Ymd',$time);
	$softid = $_POST['softid'];
	$limit_key = "{$prefix}:{$active_id}:lottery_num_limit:".$uid.":".$day;
	$now_num_day = $redis->get($limit_key);
	if($now_num_day === null){
		$redis->set($limit_key,1,86400);
		$now_num_day = 1;
	}else{
		$now_num_day = $redis->setx('incr',$limit_key);
	}
	//单日单用户上限次数
	if($now_num_day <= 3){
		//一个软件下载一次给一次抽奖机会
		$soft_key = "{$prefix}:{$active_id}:limit_softid:".$uid.":".$softid;
		$res = $redis->setnx($soft_key,1,86400);	
		//file_put_contents('/tmp/april_fool.log',$res);
		if($res == true){
			$ret = add_down_lottery_num($uid,$active_id);
			//file_put_contents('/tmp/ranking.log',$ret);
			if($ret){
				exit(json_encode(array('code' => 3)));
			}
		}
	}
	exit(json_encode(array('code' => 1)));
}

