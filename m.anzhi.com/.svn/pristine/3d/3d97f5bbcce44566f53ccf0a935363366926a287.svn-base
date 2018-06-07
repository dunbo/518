<?php
include_once ('./fun.php');
session_begin($sid);
if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
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
	$post_softid = $_POST['softid'];
	$softid = $redis->get("real_softid:post_old_softid_{$post_softid}:");
	if(!$softid){
		$softid = get_real_softid($post_softid);
	}
	$num_day = $redis->get('ranking_lottery_num_limit'.$uid.$active_id.$today);
	if($num_day === null){
		$redis->set('ranking_lottery_num_limit'.$uid.$active_id.$today,1,86400);
		$num_day = 1;
	}else{
		$num_day = $redis->setx('incr','ranking_lottery_num_limit'.$uid.$active_id.$today,+1);
	}
	//单日单用户上限次数
	list($ranking_config,$activity_arr) = get_config($active_id);
	if($num_day <= $ranking_config['like_limit']){
		//一个软件下载一次给一次抽奖机会
		$res = $redis->setnx('ranking_lottery_num'.$uid.$active_id.$softid.$today,1,86400);	
		if($res === true){
			$ret = add_down_lottery_num($uid,$active_id);
			//file_put_contents('/tmp/ranking.log',$ret);
			if($ret){
				exit(json_encode(array('code' => 3)));
			}
		}
	}
	exit(json_encode(array('code' => 1)));
}

