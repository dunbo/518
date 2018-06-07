<?php
include_once ('./fun.php');
session_begin();
$build_query = http_build_query($_GET);

if( $configs['is_test'] ) {
	$h_str = 'dev.';
}

$tplObj -> out['is_test'] = $configs['is_test'];

$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$activity_host."/lottery/{$prefix}/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
//日志
$log_data = array(
		"imsi"		=>	$_SESSION['USER_IMSI'],
		"device_id"	=>	$_SESSION['DEVICEID'],
		"activity_id"	=>	$active_id,
		"ip"	=>	$_SERVER['REMOTE_ADDR'],
		"sid"	=>	$sid,
		"time"	=>	$time,
		"user"	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
		'uid'	=>	$_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'key'	=>	'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
user_loging_new();
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	//登录日志
	$log_data = array(
		'uid'			=>	$_SESSION['USER_UID'],
		'imsi'			=>	$_SESSION['USER_IMSI'],
		'device_id'		=>	$_SESSION['DEVICEID'],
		'activity_id'	=>	$active_id,
		'ip'			=>	$_SERVER['REMOTE_ADDR'],
		'sid'			=>	$sid,
		'time'			=>	$time,
		'key'			=>	'login'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$uid = $_SESSION['USER_UID'];
	$activity = get_config($active_id);
	$data = array(
		'type'		=>	3,
		'sTime'		=>	date('Y-m-d H:i:s', $activity['start_tm']),
		'eTime'		=>	date('Y-m-d H:i:s', $activity['end_tm']),
		'yesUid'	=>	array($uid),
	);
	//消费安智币
	$xfmoney = get_xf_info($data);
	//获取安智币
	$azmoney = get_azb_info($uid, $active_id);
	$tplObj -> out['xfmoney'] = intval($xfmoney/10);
	$tplObj -> out['azmoney'] = $azmoney;
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;
}else {//未登录
	$activity = get_config($active_id);
	$tplObj -> out['is_login'] = 2;
}

if($_GET['stop'] == 1) {
	$award_key	=	"{$prefix}:{$active_id}_end_page";
	$expire		=	3600;
	$award_list	=	$redis -> get($award_key);
	if( empty($award_list) ) {
		$data = array(
				'type'		=>	3,
				'sTime'		=>	date('Y-m-d H:i:s', $activity['start_tm']),
				'eTime'		=>	date('Y-m-d H:i:s', $activity['end_tm']),
		);
		//消费安智币
		$xfmoney = get_ranking_data($data);
		array_multisort(array_column($xfmoney,'payAmount'),SORT_DESC,$xfmoney);
		$award_list = array();
		foreach ($xfmoney as $key => $val) {
			if($val['payAmount'] >= 100000) {
				if($key < 10) {
					if($key == 0) {
						$rk = '冠军';
					}elseif($key == 1) {
						$rk = '亚军';
					}elseif($key == 2) {
						$rk = '季军';
					}elseif($key == 3) {
						$rk = '第四名';
					}elseif($key == 4) {
						$rk = '第五名';
					}elseif($key == 5) {
						$rk = '第六名';
					}elseif($key == 6) {
						$rk = '第七名';
					}elseif($key == 7) {
						$rk = '第八名';
					}elseif($key == 8) {
						$rk = '第九名';
					}elseif($key == 9) {
						$rk = '第十名';
					}
					$username = get_by_username($val['uid']);
					$award_list[$key]['rk']= $rk;
					$award_list[$key]['username'] = str_replace_cn($username, 1, -2 );
					$award_list[$key]['payAmount'] = number_format($val['payAmount']/100, 2);
				}else {
					$username = get_by_username($val['uid']);
					$award_list[$key]['rk']= "&nbsp;&nbsp;&nbsp;";
					$award_list[$key]['username'] = str_replace_cn($username, 1, -2 );
					$award_list[$key]['payAmount'] = number_format($val['payAmount']/100, 2);
				}
			}
		}
		$redis	->	set($award_key, $award_list, $expire);
	}
	
	$tplObj -> out['award_list'] = $award_list;
	$tpl = "lottery/{$prefix}/end.html";
}else {
	$tpl = "lottery/{$prefix}/index.html";
}

$tplObj -> out['aid'] = $active_id;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['stop'] = $_GET['stop'];
$tplObj -> out['prefix'] = $prefix;
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];

$tplObj -> display($tpl);