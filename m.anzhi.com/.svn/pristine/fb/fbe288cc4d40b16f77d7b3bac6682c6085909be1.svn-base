<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/xy2/yd_index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;
$active_id = $_GET['aid'];
$sid = $_GET['sid'];
session_begin();
$time = time();
//日志
$log_data = array(
		"imsi" => $_SESSION['USER_IMSI'],
		"device_id" => $_SESSION['DEVICEID'],
		"activity_id" => $active_id,
		"ip" => $_SERVER['REMOTE_ADDR'],
		"sid" => $sid,
		"time" => $time,
		"user" => $_SESSION['USER_NAME'] ? $_SESSION['USER_NAME'] : '',
		'uid'=> $_SESSION['USER_UID'] ? $_SESSION['USER_UID'] : '',
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

                //插入用户信息
                $option = array(
                        'where' => array(
                                'uid' => $uid,
                                'aid' => $active_id,
                        ),
                        'table' => 'xy2_draw_userinfo',
                );
                $is_exist = $model->findOne($option,'lottery/lottery');
                if($is_exist===false){
                    $new_data = array(
                                    'uid' => $uid,
                                    'username' => $_SESSION['USER_NAME'],
                                    'create_tm' => time(),
                                    'aid' => $active_id,
                                    '__user_table' => 'xy2_draw_userinfo'
                    );
                    $model->insert($new_data,'lottery/lottery');
                }


		$tplObj -> out['username'] = $_SESSION['USER_NAME'];
		$tplObj -> out['is_login'] = 1;
		$tplObj -> out['uid'] = $uid;
}else{//未登录
	$tplObj -> out['is_login'] = 2;
}
if($_GET['stop'] == 1){
    //中奖记录
$option = array(
	'where' => array(
		'type' => 1,
	),
	'table' => 'xy2_draw_award',
	'field' => 'username,prizename',
	'cache_time' => 86400,
);
$egg_list = $model->findAll($option,'lottery/lottery');

foreach($egg_list as $k=>$v){
    $egg_list_new[$k]['username'] = str_replace_cn_new($v['username'],1,-2);
    $egg_list_new[$k]['prizename'] = $v['prizename'];
}


$tplObj -> out['egg_list'] = $egg_list_new;

$option = array(
	'where' => array(
		'type' => 2,
	),
	'table' => 'xy2_draw_award',
	'field' => 'username,prizename',
	'cache_time' => 86400,
);
$fan_list = $model->findAll($option,'lottery/lottery');
foreach($fan_list as $k=>$v){
    $fan_list_new[$k]['username'] = str_replace_cn_new($v['username'],1,-2);
    $fan_list_new[$k]['prizename'] = $v['prizename'];
}
$tplObj -> out['fan_list'] = $fan_list_new;

	$tpl = "lottery/xy2/yd_end.html";
}else{
	$tpl = "lottery/xy2/yd_index.html";
}


$this_day=date('Ymd')-20160101+1;

$sign_str = $redis->get('yd:sign_str:uid:'.$uid,$sign_str);
if(is_null($sign_str)){
    $option = array(
            'where' => array(
                    'uid' => $uid,
            ),
            'table' => 'xy2_draw_userinfo',
            'field' => 'sign_str',
    );
    $sign = $model->findOne($option,'lottery/lottery');
    $redis->set('yd:sign_str:uid:'.$uid,$sign['sign_str'],86400*6);
    $sign_str = $sign['sign_str'];
}


$rs = strpos($sign_str,"1");
if($rs==false){
        $spot1='spot_gray';
    if(1<$this_day){
        //灰色
        $star1='star_gray';
    }else{
        //白色
        $star1='star_lightgray';
    }
}else{
    //金色
        $star1='star_gold';
        $spot1='spot_gold';
}

$rs = strpos($sign_str,"2");
if($rs==false){
        $spot2='spot_gray';
    if(2<$this_day){
        //灰色
        $star2='star_gray';
    }else{
        //白色
        $star2='star_lightgray';
    }
}else{
    //金色
        $star2='star_gold';
        $spot2='spot_gold';
}

$rs = strpos($sign_str,"3");
if($rs==false){
        $spot3='spot_gray';
    if(3<$this_day){
        //灰色
        $star3='star_gray';
    }else{
        //白色
        $star3='star_lightgray';
    }
}else{
    //金色
        $star3='star_gold';
        $spot3='spot_gold';
}

$rs = strpos($sign_str,"4");
if($rs==false){
    $spot4='spot_gray';
    if(4<$this_day){
        //灰色
        $star4='star_gray';
    }else{
        //白色
        $star4='star_lightgray';
    }
}else{
    //金色
        $star4='star_gold';
        $spot4='spot_gold';
}

$rs = strpos($sign_str,"5");
if($rs==false){
        $spot5='spot_gray';
    if(5<$this_day){
        //灰色
        $star5='star_gray';
    }else{
        //白色
        $star5='star_lightgray';
    }
}else{
    //金色
        $star5='star_gold';
        $spot5='spot_gold';
}

$tplObj -> out['star1'] = $star1;
$tplObj -> out['star2'] = $star2;
$tplObj -> out['star3'] = $star3;
$tplObj -> out['star4'] = $star4;
$tplObj -> out['star5'] = $star5;
$tplObj -> out['spot1'] = $spot1;
$tplObj -> out['spot2'] = $spot2;
$tplObj -> out['spot3'] = $spot3;
$tplObj -> out['spot4'] = $spot4;



$is_sign = $redis->get('yd:sign:uid:'.$uid.':date:'.date('Ymd'));
$tplObj -> out['is_sign'] = $is_sign;
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);
