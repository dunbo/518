<?php
include_once ('./fun.php');
session_begin($sid);
if($_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176){//已登录
	$uid = $_SESSION['USER_UID'];
}else{//未登录 跳转到首页
	$url = $activity_host."/lottery/{$prefix}/index.php?aid={$active_id}&sid={$sid}";
	if($_POST){
		exit(json_encode(array('code'=>2,'url'=>$url)));
	}else{
		header("Location: {$url}");
	}
}
$config = get_config($active_id);
//分享
if($_POST['share'] == 1){
	brush_second_do($active_id);
	if($config['share_add_all'] == 1){
		//每日第一次分享给抽奖机会
		$share_num_key = "{$prefix}:".$active_id.":share_lottery_num:".$uid.":".$today;
		$res = add_lottery_num($uid,$config['lottery_num_limit'],$share_num_key,86400,$config['acrivity_day']);	
		if($res === false){
			exit(json_encode(array('code'=>1)));			
		}else{
			exit(json_encode(array('code'=>3)));			
		}	
	}
//触发下载
}else if($_POST['trigger_down'] == 1){
	$post_softid = $_POST['softid'];
	$softid = $redis->get("real_softid:post_old_softid_{$post_softid}:");
	if(!$softid){
		$softid = get_real_softid($post_softid);
	}
	$softid_key = $prefix.":".$active_id.":".$uid.":".$softid;
	$res = $redis->setnx($softid_key,1,20*86400);	
	if($res === true){
		//下载给抽奖机会
		$down_num_key = "{$prefix}:".$active_id.":trigger_down_lottery_num:".$uid.":".$softid;
		$res = add_lottery_num($uid,$config['lottery_num_limit'],$down_num_key,86400,$config['acrivity_day']);
		if($res === false){
			exit(json_encode(array('code'=>1)));			
		}else{
			exit(json_encode(array('code'=>3)));			
		}		
	}
//下载完成	
}else if($_POST['down'] == 1){
	$post_softid = $_POST['softid'];
	$softid = $redis->get("real_softid:post_old_softid_{$post_softid}:");
	if(!$softid){
		$softid = get_real_softid($post_softid);
	}
	$softid_key = $prefix.":".$active_id.":".$uid.":".$softid;
	$res = $redis->setnx($softid_key,1,20*86400);
}else if($_POST['is_complete'] == 1){
	$post_softid = $_POST['softid'];
	$softid = $redis->get("real_softid:post_old_softid_{$post_softid}:");
	if(!$softid){
		$softid = get_real_softid($_GET['softid']);
	}
	$softid_key = $prefix.":".$active_id.":".$uid.":".$softid;
	$res = $redis->get($softid_key);
	if($res){
		//下载完成给抽奖机会
		$down_num_key = "{$prefix}:".$active_id.":completed_down_lottery_num:".$uid.":".$softid;
		//file_put_contents('/tmp/act_z.log',"limit=====:".$config['lottery_num_limit']."\n",FILE_APPEND);
		$res = add_lottery_num($uid,$config['lottery_num_limit'],$down_num_key,86400,$config['acrivity_day']);	
		if($res === false){
			exit(json_encode(array('code'=>1)));			
		}else{
			exit(json_encode(array('code'=>3)));			
		}
	}	
}

