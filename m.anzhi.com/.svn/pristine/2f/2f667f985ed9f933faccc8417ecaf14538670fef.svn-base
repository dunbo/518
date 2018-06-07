<?php
include_once ('./fun.php');
$build_query = http_build_query($_GET);
if($_SERVER['SERVER_ADDR'] == '118.26.203.23' ){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url."http://promotion.anzhi.com/lottery/xy2/yx_index.php?".$build_query;
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

                //插入用户信息
                $option = array(
                        'where' => array(
                                'uid' => $uid,
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
		'type' => 2,
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

	$tpl = "lottery/xy2/yx_end.html";
}else{
	$tpl = "lottery/xy2/yx_index.html";
}


        $option = array(
                'where' => array(
                        'type' => 2,
                        'status' => 1,
                        'aid' => $active_id
                ),
                'table' => 'xy2_draw_award',
                'field' => 'username,prizename',
                'order' => 'id',
                'cache_time'=>1200,
        );
        $prizeuser = $model->findAll($option,'lottery/lottery');
        if(empty($prizeuser)){
                $lunbo = '活动已开启，现在刮卡吧！还可立即抽奖！';
        }else{
            foreach($prizeuser as $v){
                $lunbo = $lunbo.'恭喜用户'.str_replace_cn_new($v['username'],1,-2).',好运爆表,抽奖获得'.$v['prizename'].';';
            }
        }


	$tplObj -> out['lunbo'] = $lunbo;

$za_num = $redis->get('yd:sign:lottery_num:uid:'.$uid);
if(empty($za_num)){
    $za_num =0;
}

$user_today_shangxian_share_key = 'yd:share:shangxian:uid:'.$uid.':date:'.date('Ymd');
$is_share = $redis->get($user_today_shangxian_share_key);
if(empty($is_share)){
    $is_share =0;
}
									

if(date('Ymd')=='20160222'){
    $share_text = '我今天刮卡刮中了：1个韭菜汤圆，那酸爽，简直无法想象！';
}else if(date('Ymd')=='20160224'){
    $share_text = '我今天刮卡刮中了：1个榴莲汤圆，好吃到飞起，你值得拥有！';
}else{
    $share_text = '我今天刮卡刮中了：1个老干妈汤圆，正宗老味道，还是熟悉的配方！';
}
$gu_share = '一起嗨皮闹元宵，猛戳这里来刮卡，试试你的手气！';

    	
									
    

	$tplObj -> out['share_text'] = $share_text.$gu_share;
	$tplObj -> out['is_share'] = $is_share;

	$tplObj -> out['share'] = $_GET['share'];
	$tplObj -> out['za_num'] = $za_num;

	//用户信息
	$userinfo = get_user_info_new($uid,$active_id,'yd','xy2_draw_userinfo');
	$tplObj -> out['phone'] = $userinfo['phone'];	
	$tplObj -> out['contact_name'] = $userinfo['contact_name'];	
	$tplObj -> out['address'] = $userinfo['address'];	$is_sign = $redis->get('yd:sign:uid:'.$uid.':date:'.date('Ymd'));
$tplObj -> out['is_sign'] = $is_sign;
$tplObj -> out['aid'] = $active_id;
$tplObj -> out['now'] = $time;
$tplObj -> out['sid'] = $_GET['sid'];

$pic = 'http://img3.anzhi.com/static/activity/lantern2016/images/scratch_01.png';
$arr = getimagesize($pic);
$pic = "data:{$arr['mime']};base64," . base64_encode(file_get_contents($pic));  
$tplObj -> out['ppic'] = $pic;

$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];
$tplObj -> out['version_code'] = $_SESSION['VERSION_CODE'];
$tplObj -> display($tpl);
