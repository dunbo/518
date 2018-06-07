<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/ghost/index.php?lgfrom=1&".$build_query;
$tplObj -> out['login_url'] = $login_url;
session_begin();
$time = time();
//$time = get_now_time();
//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_NAME'] : '',
		'uid'=> $_SESSION['USER_UID'] && $_SESSION['USER_ID'] != 13176 ? $_SESSION['USER_UID'] : '',
		'key' => 'show_homepage'
);
permanentlog('activity_'.$active_id.'.log', json_encode($log_data));	
user_loging_new();
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录
	//登录日志
	$log_data = array(
		'uid' => $_SESSION['USER_UID'],
		'imsi' => $_SESSION['USER_IMSI'],
		'device_id' => $_SESSION['DEVICEID'],
		'activity_id' => $active_id,
		'ip' => $_SERVER['REMOTE_ADDR'],
		'sid' => $sid,
		'time' => $time,
		'key' => 'login'
	);
	permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	$uid = $_SESSION['USER_UID'];
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $uid;

        //todo 是否预约
        $res = $redis->get("ghost:{$active_id}_is_sign:".$uid);
	if($res == 1){
		$tplObj -> out['is_sing'] = 1;
	}else{
                if($_GET['lgfrom']==1){
                        $time = get_now_time();
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
		        $tplObj -> out['is_sing'] = 1;
                }else{
		    $tplObj -> out['is_sing'] = 2;
                }
	}


	$kind_award_list = get_user_kind_award_new($uid,$active_id,'ghost','valentine_draw_award');//获取该用户实物
	$kind_award_gift = get_user_kind_gift_new($uid,$active_id,"ghost","valentine_draw_gift");//获取该用户礼包
	if(!$kind_award_list && !$kind_award_gift){
		$tplObj -> out['is_user_winning'] = 2;//无中奖信息	
	}else{
		$tplObj -> out['is_user_winning'] = 1;
	}
	$tplObj -> out['lottery_num'] = get_lottery_num($active_id,$uid);
}else{//未登录
        $tplObj -> out['is_sing'] = 2;
	$tplObj -> out['is_login'] = 2;
	$tplObj -> out['lottery_num'] = 1;	
}

//$tplObj -> out['award_all'] = get_award_all_new($active_id,"ghost",'valentine_draw_award');//获取获奖人信息奖品  轮播

if($_GET['stop'] == 1){
	$tpl = "lottery/ghost/end.html";
}else{
	$tpl = "lottery/ghost/index.html";
}

$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);
