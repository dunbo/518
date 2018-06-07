<?php

require_once(dirname(realpath(__FILE__)) . '/init_page.php');
//opt 1.美女	2.猎奇
$opt	=	(int)$_GET['opt'];
$step	=	(int)$_GET['step'];
$type	=	$opt.'-'.$step;

$tplObj -> out['opt']	=	$opt;
$tplObj -> out['step']	=	$step;

if( $opt == 1 ) {
	if( $step == 1 ) {
		//下载软件解锁
		$log_data = array(
				'imsi'			=>	$_SESSION['USER_IMSI'],
				'device_id'		=>	$_SESSION['DEVICEID'],
				'activity_id'	=>	$aid,
				'sid'			=>	$sid,
				'ip'			=>	$_SERVER['REMOTE_ADDR'],
				'time'			=>	time(),
				'users'			=>	'',
				'uid'			=>	'',
				'key'			=>	'download_page',
				'type'			=>	$type,
		);
		permanentlog($activity_log_file, json_encode($log_data));
		$tplObj -> out['category'] = $category[0];
		$res = get_unlock($imsi, $opt, $step);
		if($res) {
			$tplObj -> out['down_num']	=	1;
		}else {
			$tplObj -> out['down_num']	=	0;
		}
		$tplObj->display('lottery/beauty_puzzle/step_1_1.html');
	}elseif( $step == 2 ) {
		//拼图
		$log_data = array(
				'imsi'			=>	$_SESSION['USER_IMSI'],
				'device_id'		=>	$_SESSION['DEVICEID'],
				'activity_id'	=>	$aid,
				'sid'			=>	$sid,
				'ip'			=>	$_SERVER['REMOTE_ADDR'],
				'time'			=>	time(),
				'users'			=>	'',
				'uid'			=>	'',
				'key'			=>	'puzzle_page',
				'type'			=>	$type,
		);
		permanentlog($activity_log_file, json_encode($log_data));
		//拼图
		$pictrue = get_cur_conf($opt, 1);
		//随机但不重复
		$tplObj -> out['pictrue'] = $pictrue;
		$tplObj->display('lottery/beauty_puzzle/step_1_2.html');
	}elseif( $step == 3 ) {
		//下载软件解锁
		$log_data = array(
				'imsi'			=>	$_SESSION['USER_IMSI'],
				'device_id'		=>	$_SESSION['DEVICEID'],
				'activity_id'	=>	$aid,
				'sid'			=>	$sid,
				'ip'			=>	$_SERVER['REMOTE_ADDR'],
				'time'			=>	time(),
				'users'			=>	'',
				'uid'			=>	'',
				'key'			=>	'download_page',
				'type'			=>	$type,
		);
		permanentlog($activity_log_file, json_encode($log_data));
		$res = get_unlock($imsi, $opt, $step);
		if($res) {
			$tplObj -> out['down_num']	=	1;
		}else {
			$tplObj -> out['down_num']	=	0;
		}
		$tplObj -> out['category'] = $category[1];
		$tplObj->display('lottery/beauty_puzzle/step_1_3.html');
	}elseif( $step == 4 ) {
		//观看美女视频
		$log_data = array(
				'imsi'			=>	$_SESSION['USER_IMSI'],
				'device_id'		=>	$_SESSION['DEVICEID'],
				'activity_id'	=>	$aid,
				'sid'			=>	$sid,
				'ip'			=>	$_SERVER['REMOTE_ADDR'],
				'time'			=>	time(),
				'users'			=>	'',
				'uid'			=>	'',
				'key'			=>	'beauty_video_page',
				'type'			=>	$type,
		);
		permanentlog($activity_log_file, json_encode($log_data));
		$video = get_cur_conf($opt, 3);
		$tplObj -> out['video'] = $video;
		$tplObj->display('lottery/beauty_puzzle/step_1_4.html');
	}
}elseif( $opt == 2 ) {
	if( $step == 1 ) {
		//下载软件解锁
		$log_data = array(
				'imsi'			=>	$_SESSION['USER_IMSI'],
				'device_id'		=>	$_SESSION['DEVICEID'],
				'activity_id'	=>	$aid,
				'sid'			=>	$sid,
				'ip'			=>	$_SERVER['REMOTE_ADDR'],
				'time'			=>	time(),
				'users'			=>	'',
				'uid'			=>	'',
				'key'			=>	'download_page',
				'type'			=>	$type,
		);
		permanentlog($activity_log_file, json_encode($log_data));
		$res = get_unlock($imsi, $opt, $step);
		if($res) {
			$tplObj -> out['down_num']	=	1;
		}else {
			$tplObj -> out['down_num']	=	0;
		}
		$tplObj -> out['category'] = $category[2];
		$tplObj->display('lottery/beauty_puzzle/step_2_1.html');
	}elseif( $step == 2 ) {
		//观看猎奇动画
		$log_data = array(
				'imsi'			=>	$_SESSION['USER_IMSI'],
				'device_id'		=>	$_SESSION['DEVICEID'],
				'activity_id'	=>	$aid,
				'sid'			=>	$sid,
				'ip'			=>	$_SERVER['REMOTE_ADDR'],
				'time'			=>	time(),
				'users'			=>	'',
				'uid'			=>	'',
				'key'			=>	'lieqi_animation_page',
				'type'			=>	$type,
		);
		permanentlog($activity_log_file, json_encode($log_data));
		$tplObj -> out['pictrue'] = $conf_arr_2_1;//猎奇动画
		$tplObj->display('lottery/beauty_puzzle/step_2_2.html');
	}elseif( $step == 3 ) {
		//下载软件解锁
		$log_data = array(
				'imsi'			=>	$_SESSION['USER_IMSI'],
				'device_id'		=>	$_SESSION['DEVICEID'],
				'activity_id'	=>	$aid,
				'sid'			=>	$sid,
				'ip'			=>	$_SERVER['REMOTE_ADDR'],
				'time'			=>	time(),
				'users'			=>	'',
				'uid'			=>	'',
				'key'			=>	'download_page',
				'type'			=>	$type,
		);
		permanentlog($activity_log_file, json_encode($log_data));
		$res = get_unlock($imsi, $opt, $step);
		if($res) {
			$tplObj -> out['down_num']	=	1;
		}else {
			$tplObj -> out['down_num']	=	0;
		}
		$tplObj -> out['category'] = $category[3];
		$tplObj->display('lottery/beauty_puzzle/step_2_3.html');
	}elseif( $step == 4 ) {
		//观看美女视频
		$log_data = array(
				'imsi'			=>	$_SESSION['USER_IMSI'],
				'device_id'		=>	$_SESSION['DEVICEID'],
				'activity_id'	=>	$aid,
				'sid'			=>	$sid,
				'ip'			=>	$_SERVER['REMOTE_ADDR'],
				'time'			=>	time(),
				'users'			=>	'',
				'uid'			=>	'',
				'key'			=>	'lieqi_video_page',
				'type'			=>	$type,
		);
		permanentlog($activity_log_file, json_encode($log_data));
		$video = get_cur_conf($opt, 3);
		$tplObj -> out['video'] = $video;
		$tplObj->display('lottery/beauty_puzzle/step_2_4.html');
	}
}
