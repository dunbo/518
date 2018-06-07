<?php
include_once ('./fun.php');

/*
$ip =$_SERVER["REMOTE_ADDR"]; 

$ip_array=array(
	"218.241.82.226",
	"218.241.82.231",
);
if(!in_array($ip,$ip_array)){
    exit(0);
}
 */

$build_query = http_build_query($_GET);
if($configs['is_test'] == 1){
	$h_str = 'dev.';
	$tplObj -> out['is_test'] = 1;
}
$center_url = "http://".$h_str."i.anzhi.com/mweb/account/login?serviceId=014&serviceVersion=5410&serviceType=0&redirecturi=";
$login_url = $center_url.$configs['activity_url']."lottery/red_package/index.php?".$build_query;
$tplObj -> out['login_url'] = $login_url;	
$share = $_GET['share'];
$tplObj->out ['prefix_url'] = $configs['activity_url'];

brush_second_do($aid);
get_brush_bysn();

if($_POST){
    $ppackage = $_POST['ppackage'];
    $orderid = $_POST['orderid'];
    $aid = $_POST['aid'];
    $time=time();
    if($ppackage){
        $tmp_arr = get_app_info($ppackage);
        $log_data = array(
                        "package" => $ppackage,
                        "activity_id" => $aid,
                        "appinfo" => $tmp_arr,
                        "time" => $time,
                        'key' => 'appinfo'
        );

        permanentlog('activity_'.$aid.'.log', json_encode($log_data));
        echo json_encode($tmp_arr);
        exit(0);
    }else if($orderid){
        $uid = $_SESSION['USER_UID'];
        $key = "red_package:".$aid.":uid:".$uid.":red_package_list";//未领取红包列表
        $log_data = array(
                        "orderid" => $orderid,
                        "activity_id" => $aid,
                        "time" => $time,
                        "uid" => $uid,
                        'key' => 'guolv'
        );
        permanentlog('activity_'.$aid.'.log', json_encode($log_data));
        $ret = $redis->hdel($key,$orderid);
		exit(json_encode($ret));
    }
}

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

//防刷用
$home_key_today = 'gen_pre_brush:home:uid:'.$uid.':aid:'.$aid.':'.date('Ymd',time());
$redis->setnx($home_key_today,time());
$redis->expire($home_key_today,86400);


$activity_option = array(
	'where' => array(
		'id' => $aid
	),
	'cache_time' => 180,
	'table' => 'sj_activity'
);
$activity_result = $model -> findOne($activity_option);
if($activity_result['activity_type']!=5){
    exit(0);
}
$redis->set($down_set_num,$activity_result['red_chance_num'],86400);

//初始抽奖次数 todo

//$send_num = 3; //todo 从sj_activity表里读的
$send_num = $activity_result['red_init_num']; //todo 从sj_activity表里读的
user_loging_new();
if(isset($_SESSION['USER_UID']) && $_SESSION['USER_ID'] != 13176){//已登录

        $key = "red_package:".$aid.":uid:".$uid.":red_package_list";//未领取红包列表
        $temp_list = $redis->gethash($key);

        foreach($temp_list as $k=>$v){
            if(time()-$v['time']>=3600){
                $redis->hdel($key,$k);
            }
        }

        $nogetlist = $redis->gethash($key);
        krsort($nogetlist);
        $tplObj -> out['nogetlist'] = $nogetlist;
        if(!empty($nogetlist)){
            $tplObj -> out['noget'] = 1;
        }

        $tmp = $redis->gethash($p_key);
        $package_arr_task = array_keys($tmp);

        $tmp2 = $redis->gethash($p_done_key);
        $package_arr_task_down = array_keys($tmp2);
        //var_dump($p_key,$p_done_key,$package_arr_task,$package_arr_task_down);
        $tplObj -> out['package_arr_task'] = json_encode($package_arr_task);
        $tplObj -> out['package_arr_task_down'] = json_encode($package_arr_task_down);

	if($_GET['is_register'] == 1){
		//注册成功日志
		$log_data = array(
			'uid' => $_SESSION['USER_UID'],
			'imsi' => $_SESSION['USER_IMSI'],
			'device_id' => $_SESSION['DEVICEID'],
			'activity_id' => $active_id,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'sid' => $sid,
			'time' => $time,
			'key' => 'register'
		);
		permanentlog('activity_'.$active_id.'.log', json_encode($log_data));
	}else{	
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
	}
	$tplObj -> out['username'] = $_SESSION['USER_NAME'];
	$tplObj -> out['is_login'] = 1;
	$tplObj -> out['uid'] = $_SESSION['USER_UID'];

		$notexist_t3 = $redis->setnx($is_send_num,$send_num);
		$redis->expire($is_send_num,86400*90);
		if($notexist_t3===true){
                        //赠送次数
			$now_num = $redis -> setx('incr',$lottery_num_key,$send_num);
                        $redis->expire($imsi_num,86400*90);
			$where = array(
				'aid' => $active_id,
				'uid' => $uid
			);
			$data = array(
				'lottery_num' => $now_num,
				'update_tm' => time(),
				'__user_table' => 'red_package_lottery_num'
			);
			$result = $model -> update($where,$data,'lottery/lottery');
			if(!$result)
			{
				$data = array(
					'aid' => $active_id,
					'uid' => $uid,
					'username' => $username,
					'imsi' => $imsi,
					'imei' => $imei,
                                        'mac' => $_SESSION['MAC'],
					'lottery_num' => $now_num,
					'update_tm' => time(),
					'__user_table' => 'red_package_lottery_num'
				);
				$result = $model -> insert($data,'lottery/lottery');
                        }
                }

			$down_num = $redis -> get($down_num_key);
                        if(empty($down_num)){
                            $down_num=0;
                        }
			$now_num = $redis -> get($lottery_num_key);
                        $tplObj -> out['down_num'] = $down_num;
                        $tplObj -> out['now_num'] = $now_num;
}else{//未登录
        $tplObj -> out['down_num'] = 0;
        $tplObj -> out['now_num'] = $send_num;
        $tplObj -> out['package_arr_task'] = json_encode(array());
        $tplObj -> out['package_arr_task_down'] = json_encode(array());
	$tplObj -> out['is_login'] = 2;
}


//奖品展示
$prize_option = array(
	'where' => array(
		'aid' => $aid
	),
        'field' => 'name,id,level,type',
	//'cache_time' => 300,
	'table' => 'sign_prize'
);
$prize_result = $model -> findAll($prize_option,'lottery/lottery');
//echo $model->getsql();
foreach($prize_result as $key => $val){
	$prize_level[] = $val['level'];
}
array_multisort($prize_level,SORT_ASC,$prize_result);



$tplObj -> out['is_share'] = $_GET['is_share'];

$qianzhui = getImageHost();
$tplObj -> out['share'] = $share;
$tplObj -> out['imgurl'] = $qianzhui;
$tplObj -> out['lottery_num'] = $now_num;
$tplObj -> out['prize_results'] = $prize_result;
$tplObj -> out['prize_result_str'] = json_encode($prize_result);
$activity_result -> out['prize_count'] = count($prize_result);
$activity_result['red_at_desc'] = str_replace(array("\r\n", "\r", "\n"), "", htmlspecialchars_decode($activity_result['red_at_desc']));
$activity_result['red_at_desc'] = str_replace("'",'"',$activity_result['red_at_desc']);

$tplObj -> out['activity_result'] = $activity_result;
$tplObj -> out['imsi'] = $_SESSION['USER_IMSI'];
$tplObj -> out['sid'] = $_GET['sid'];
$version_code = $_SESSION['VERSION_CODE'];
$tplObj -> out['version_code'] = $version_code;
$tplObj -> out['aid'] = $aid;
$tplObj -> out['static_url'] = $configs['static_url'];
$tplObj -> out['new_static_url'] = $configs['new_static_url'];

//$tplObj -> display('lottery/red_package/lottery_nine.html');
$tplObj -> display('lottery/red_package/index.html');
